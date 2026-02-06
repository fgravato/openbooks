<?php

declare(strict_types=1);

namespace App\Domains\Payments\Gateways;

use App\Domains\Payments\Exceptions\GatewayException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class StripeGateway implements PaymentGatewayInterface
{
    private const BASE_URL = 'https://api.stripe.com/v1';

    /**
     * @return array<string, mixed>
     */
    public function createPaymentIntent(int $amount, string $currency, string $paymentMethodId): array
    {
        $response = $this->client()
            ->asForm()
            ->withHeaders([
                'Idempotency-Key' => $this->idempotencyKey("payment_intent_create_{$paymentMethodId}_{$amount}"),
            ])
            ->post(self::BASE_URL.'/payment_intents', [
                'amount' => $amount,
                'currency' => strtolower($currency),
                'payment_method' => $paymentMethodId,
                'confirmation_method' => 'automatic',
                'confirm' => 'true',
            ]);

        if (! $response->successful()) {
            throw new GatewayException('Stripe', (string) $response->json('error.message', 'Unable to create payment intent.'));
        }

        /** @var array<string, mixed> $payload */
        $payload = $response->json();

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function confirmPaymentIntent(string $paymentIntentId): array
    {
        $response = $this->client()
            ->asForm()
            ->withHeaders([
                'Idempotency-Key' => $this->idempotencyKey("payment_intent_confirm_{$paymentIntentId}"),
            ])
            ->post(self::BASE_URL."/payment_intents/{$paymentIntentId}/confirm");

        if (! $response->successful()) {
            throw new GatewayException('Stripe', (string) $response->json('error.message', 'Unable to confirm payment intent.'));
        }

        /** @var array<string, mixed> $payload */
        $payload = $response->json();

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function refundCharge(string $chargeId, int $amount): array
    {
        $response = $this->client()
            ->asForm()
            ->withHeaders([
                'Idempotency-Key' => $this->idempotencyKey("refund_{$chargeId}_{$amount}"),
            ])
            ->post(self::BASE_URL.'/refunds', [
                'payment_intent' => $chargeId,
                'amount' => $amount,
            ]);

        if (! $response->successful()) {
            throw new GatewayException('Stripe', (string) $response->json('error.message', 'Unable to refund charge.'));
        }

        /** @var array<string, mixed> $payload */
        $payload = $response->json();

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function getPaymentMethod(string $paymentMethodId): array
    {
        $response = $this->client()->get(self::BASE_URL."/payment_methods/{$paymentMethodId}");

        if (! $response->successful()) {
            throw new GatewayException('Stripe', (string) $response->json('error.message', 'Unable to fetch payment method.'));
        }

        /** @var array<string, mixed> $payload */
        $payload = $response->json();

        return $payload;
    }

    public function createCustomer(string $email, string $name): string
    {
        $response = $this->client()
            ->asForm()
            ->post(self::BASE_URL.'/customers', [
                'email' => $email,
                'name' => $name,
            ]);

        if (! $response->successful()) {
            throw new GatewayException('Stripe', (string) $response->json('error.message', 'Unable to create customer.'));
        }

        return (string) $response->json('id');
    }

    public function attachPaymentMethod(string $paymentMethodId, string $customerId): void
    {
        $response = $this->client()
            ->asForm()
            ->post(self::BASE_URL."/payment_methods/{$paymentMethodId}/attach", [
                'customer' => $customerId,
            ]);

        if (! $response->successful()) {
            throw new GatewayException('Stripe', (string) $response->json('error.message', 'Unable to attach payment method.'));
        }
    }

    public function constructWebhookEvent(string $payload, string $sigHeader, string $secret): object
    {
        if (! $this->isValidSignature($payload, $sigHeader, $secret)) {
            throw new GatewayException('Stripe', 'Invalid webhook signature.');
        }

        /** @var object|null $event */
        $event = json_decode($payload, false, 512, JSON_THROW_ON_ERROR);

        return $event;
    }

    private function client(): PendingRequest
    {
        $secret = (string) \config('services.stripe.secret', '');

        if ($secret === '') {
            throw new GatewayException('Stripe', 'Stripe secret key is not configured.');
        }

        return Http::acceptJson()->withToken($secret)->timeout(15);
    }

    private function idempotencyKey(string $seed): string
    {
        return hash('sha256', $seed.'|'.(string) \now()->timestamp);
    }

    private function isValidSignature(string $payload, string $sigHeader, string $secret): bool
    {
        if ($sigHeader === '' || $secret === '') {
            return false;
        }

        $parts = [];

        foreach (explode(',', $sigHeader) as $segment) {
            [$key, $value] = array_pad(explode('=', trim($segment), 2), 2, null);

            if ($key !== null && $value !== null) {
                $parts[$key][] = $value;
            }
        }

        $timestamp = $parts['t'][0] ?? null;
        $signatures = $parts['v1'] ?? [];

        if ($timestamp === null || $signatures === []) {
            return false;
        }

        $signedPayload = $timestamp.'.'.$payload;
        $expectedSignature = hash_hmac('sha256', $signedPayload, $secret);

        foreach ($signatures as $signature) {
            if (hash_equals($expectedSignature, (string) $signature)) {
                return true;
            }
        }

        return false;
    }
}
