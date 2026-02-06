<?php

declare(strict_types=1);

namespace App\Domains\Payments\Gateways;

interface PaymentGatewayInterface
{
    /**
     * @return array<string, mixed>
     */
    public function createPaymentIntent(int $amount, string $currency, string $paymentMethodId): array;

    /**
     * @return array<string, mixed>
     */
    public function confirmPaymentIntent(string $paymentIntentId): array;

    /**
     * @return array<string, mixed>
     */
    public function refundCharge(string $chargeId, int $amount): array;

    /**
     * @return array<string, mixed>
     */
    public function getPaymentMethod(string $paymentMethodId): array;

    public function createCustomer(string $email, string $name): string;

    public function attachPaymentMethod(string $paymentMethodId, string $customerId): void;

    public function constructWebhookEvent(string $payload, string $sigHeader, string $secret): object;
}
