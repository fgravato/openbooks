<?php

declare(strict_types=1);

namespace App\Domains\Payments\Services;

use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Models\Payment;
use Illuminate\Support\Facades\Log;

class StripeWebhookService
{
    /**
     * @param array<string, mixed> $payload
     */
    public function handleWebhook(array $payload): void
    {
        $eventType = (string) ($payload['type'] ?? '');
        $data = (array) ($payload['data']['object'] ?? []);

        match ($eventType) {
            'payment_intent.succeeded' => $this->handlePaymentIntentSucceeded($data),
            'payment_intent.payment_failed' => $this->handlePaymentIntentFailed($data),
            'charge.refunded' => $this->handleChargeRefunded($data),
            'charge.dispute.created' => $this->handleDisputeCreated($data),
            default => null,
        };
    }

    /**
     * @param array<string, mixed> $data
     */
    public function handlePaymentIntentSucceeded(array $data): void
    {
        $payment = $this->resolvePayment((string) ($data['id'] ?? ''));

        if ($payment === null) {
            return;
        }

        $payment->status = PaymentStatus::Completed;
        $payment->paid_at = \now();
        $payment->metadata = [
            ...((array) $payment->metadata),
            'webhook' => $data,
        ];
        $payment->save();

        Log::info('Stripe payment intent succeeded webhook processed.', [
            'payment_id' => $payment->id,
            'transaction_id' => $data['id'] ?? null,
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function handlePaymentIntentFailed(array $data): void
    {
        $payment = $this->resolvePayment((string) ($data['id'] ?? ''));

        if ($payment === null) {
            return;
        }

        $payment->status = PaymentStatus::Failed;
        $payment->metadata = [
            ...((array) $payment->metadata),
            'failure' => $data,
        ];
        $payment->save();

        Log::warning('Stripe payment intent failed webhook processed.', [
            'payment_id' => $payment->id,
            'transaction_id' => $data['id'] ?? null,
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function handleChargeRefunded(array $data): void
    {
        $payment = $this->resolvePayment((string) ($data['payment_intent'] ?? $data['id'] ?? ''));

        if ($payment === null) {
            return;
        }

        $refundedAmount = (int) ($data['amount_refunded'] ?? $data['amount'] ?? 0);
        $payment->applyRefund($refundedAmount);
        $payment->metadata = [
            ...((array) $payment->metadata),
            'refund' => $data,
        ];
        $payment->save();

        Log::info('Stripe charge refunded webhook processed.', [
            'payment_id' => $payment->id,
            'transaction_id' => $data['payment_intent'] ?? null,
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function handleDisputeCreated(array $data): void
    {
        $paymentIntentId = (string) ($data['payment_intent'] ?? '');
        $payment = $this->resolvePayment($paymentIntentId);

        if ($payment === null) {
            return;
        }

        $payment->status = PaymentStatus::Failed;
        $payment->metadata = [
            ...((array) $payment->metadata),
            'dispute' => $data,
        ];
        $payment->save();

        Log::warning('Stripe dispute created webhook processed.', [
            'payment_id' => $payment->id,
            'transaction_id' => $paymentIntentId,
        ]);
    }

    private function resolvePayment(string $transactionId): ?Payment
    {
        if ($transactionId === '') {
            return null;
        }

        return Payment::query()
            ->withoutGlobalScopes()
            ->where('gateway_transaction_id', $transactionId)
            ->first();
    }
}
