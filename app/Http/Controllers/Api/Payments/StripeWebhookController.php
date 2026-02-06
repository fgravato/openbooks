<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Payments;

use App\Domains\Payments\Gateways\StripeGateway;
use App\Domains\Payments\Services\StripeWebhookService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StripeWebhookController extends Controller
{
    public function __construct(
        private readonly StripeWebhookService $stripeWebhookService,
        private readonly StripeGateway $stripeGateway,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        if (! $this->verifyWebhook($request)) {
            return \response()->json([
                'message' => 'Invalid webhook signature.',
            ], 400);
        }

        $payload = (string) $request->getContent();
        $signatureHeader = (string) $request->header('Stripe-Signature', '');
        $secret = (string) \config('services.stripe.webhook_secret', '');

        $event = $this->stripeGateway->constructWebhookEvent($payload, $signatureHeader, $secret);

        /** @var array<string, mixed> $eventPayload */
        $eventPayload = (array) $event;
        $this->stripeWebhookService->handleWebhook($eventPayload);

        return \response()->json(['received' => true]);
    }

    public function verifyWebhook(Request $request): bool
    {
        $payload = (string) $request->getContent();
        $signatureHeader = (string) $request->header('Stripe-Signature', '');
        $secret = (string) \config('services.stripe.webhook_secret', '');

        try {
            $this->stripeGateway->constructWebhookEvent($payload, $signatureHeader, $secret);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
