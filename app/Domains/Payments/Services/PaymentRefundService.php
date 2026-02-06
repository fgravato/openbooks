<?php

declare(strict_types=1);

namespace App\Domains\Payments\Services;

use App\Domains\Payments\Exceptions\InvalidRefundAmountException;
use App\Domains\Payments\Exceptions\PaymentProcessingException;
use App\Domains\Payments\Gateways\PaymentGatewayInterface;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\PaymentRefund;
use App\Domains\Invoicing\Enums\InvoiceStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentRefundService
{
    public function __construct(private readonly PaymentGatewayInterface $gateway)
    {
    }

    public function refundPayment(Payment $payment, int $amount, string $reason): PaymentRefund
    {
        if (! $this->validateRefundAmount($payment, $amount)) {
            throw new InvalidRefundAmountException('Invalid refund amount for this payment.');
        }

        return DB::transaction(function () use ($payment, $amount, $reason): PaymentRefund {
            $gatewayRefundId = null;

            if ($payment->gateway->isAutomated()) {
                $gatewayRefundId = $this->processStripeRefund($payment, $amount);
            }

            $refund = PaymentRefund::query()->create([
                'payment_id' => $payment->id,
                'organization_id' => $payment->organization_id,
                'amount' => $amount,
                'reason' => $reason,
                'gateway_refund_id' => $gatewayRefundId,
                'refunded_by_user_id' => \auth()->id(),
                'refunded_at' => \now(),
            ]);

            $payment->applyRefund($amount);

            Log::info('Payment refund processed.', [
                'payment_id' => $payment->id,
                'refund_id' => $refund->id,
                'organization_id' => $payment->organization_id,
                'amount' => $amount,
            ]);

            if ($payment->invoice !== null) {
                $invoice = $payment->invoice;
                $invoice->amount_paid = max(0, (int) $invoice->amount_paid - $amount);
                $invoice->amount_outstanding = max(0, (int) $invoice->total - (int) $invoice->amount_paid);

                if ((int) $invoice->amount_outstanding > 0 && $invoice->status->value === 'paid') {
                    $invoice->status = InvoiceStatus::Partial;
                    $invoice->paid_at = null;
                }

                $invoice->save();
            }

            return $refund;
        });
    }

    public function processStripeRefund(Payment $payment, int $amount): string
    {
        $chargeId = (string) ($payment->gateway_transaction_id ?? '');

        if ($chargeId === '') {
            throw new PaymentProcessingException('Missing gateway transaction ID for automated refund.');
        }

        $response = $this->gateway->refundCharge($chargeId, $amount);

        return (string) ($response['id'] ?? '');
    }

    public function validateRefundAmount(Payment $payment, int $amount): bool
    {
        return $amount > 0
            && $payment->isRefundable()
            && $amount <= $payment->getRemainingAmount();
    }
}
