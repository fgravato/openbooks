<?php

declare(strict_types=1);

namespace App\Domains\Payments\Services;

use App\Domains\Payments\DTOs\PaymentResult;
use App\Domains\Payments\Enums\PaymentMethod;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Exceptions\PaymentProcessingException;
use App\Domains\Payments\Gateways\PaymentGatewayInterface;
use App\Domains\Payments\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentProcessingService
{
    public function __construct(private readonly PaymentGatewayInterface $gateway)
    {
    }

    /**
     * @param array<string, mixed> $gatewayData
     */
    public function processPayment(Payment $payment, array $gatewayData): PaymentResult
    {
        try {
            if ($payment->gateway->isAutomated()) {
                $paymentMethodId = isset($gatewayData['payment_method_id'])
                    ? (string) $gatewayData['payment_method_id']
                    : null;

                if ($paymentMethodId === null || $paymentMethodId === '') {
                    throw new PaymentProcessingException('Payment method identifier is required for online payment.');
                }

                return $this->handleStripePayment($payment, $paymentMethodId);
            }

            return $this->handleOfflinePayment($payment);
        } catch (\Throwable $exception) {
            $payment->markAsFailed($exception->getMessage());

            return new PaymentResult(
                success: false,
                errorMessage: $exception->getMessage(),
                status: PaymentStatus::Failed->value,
            );
        }
    }

    public function handleStripePayment(Payment $payment, string $paymentMethodId): PaymentResult
    {
        if (! $this->validatePaymentMethod($paymentMethodId)) {
            throw new PaymentProcessingException('Invalid payment method ID.');
        }

        return DB::transaction(function () use ($payment, $paymentMethodId): PaymentResult {
            $intent = $this->gateway->createPaymentIntent(
                amount: (int) $payment->amount,
                currency: (string) $payment->currency_code,
                paymentMethodId: $paymentMethodId,
            );

            $transactionId = (string) ($intent['id'] ?? '');

            if ($transactionId === '') {
                throw new PaymentProcessingException('Stripe did not return a transaction identifier.');
            }

            $feeAmount = $this->calculateFees((int) $payment->amount, $payment->method);
            $payment->gateway_transaction_id = $transactionId;
            $payment->gateway_fee_amount = $feeAmount;
            $payment->net_amount = max(0, (int) $payment->amount - $feeAmount);
            $payment->metadata = [
                ...((array) $payment->metadata),
                'stripe' => $intent,
            ];
            $payment->markAsCompleted();

            if ($payment->invoice !== null) {
                $payment->invoice->applyPayment($payment);
            }

            Log::info('Payment completed.', [
                'payment_id' => $payment->id,
                'organization_id' => $payment->organization_id,
                'gateway' => $payment->gateway->value,
                'transaction_id' => $transactionId,
            ]);

            return new PaymentResult(
                success: true,
                transactionId: $transactionId,
                status: PaymentStatus::Completed->value,
            );
        });
    }

    public function handleOfflinePayment(Payment $payment): PaymentResult
    {
        return DB::transaction(function () use ($payment): PaymentResult {
            $feeAmount = $this->calculateFees((int) $payment->amount, $payment->method);
            $payment->gateway_fee_amount = $feeAmount;
            $payment->net_amount = max(0, (int) $payment->amount - $feeAmount);
            $payment->markAsCompleted();

            if ($payment->invoice !== null) {
                $payment->invoice->applyPayment($payment);
            }

            Log::info('Offline payment completed.', [
                'payment_id' => $payment->id,
                'organization_id' => $payment->organization_id,
            ]);

            return new PaymentResult(
                success: true,
                transactionId: $payment->gateway_transaction_id,
                status: PaymentStatus::Completed->value,
            );
        });
    }

    public function calculateFees(int $amount, PaymentMethod $method): int
    {
        return match ($method) {
            PaymentMethod::CreditCard,
            PaymentMethod::ApplePay,
            PaymentMethod::GooglePay,
            PaymentMethod::PayPal => (int) round(($amount * 0.029) + 30),
            PaymentMethod::Ach => min((int) round($amount * 0.008), 500),
            default => 0,
        };
    }

    public function validatePaymentMethod(string $paymentMethodId): bool
    {
        try {
            $paymentMethod = $this->gateway->getPaymentMethod($paymentMethodId);

            return isset($paymentMethod['id']) && (string) $paymentMethod['id'] !== '';
        } catch (\Throwable) {
            return false;
        }
    }
}
