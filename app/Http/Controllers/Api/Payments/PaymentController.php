<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Payments;

use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Payments\DTOs\PaymentData;
use App\Domains\Payments\Enums\PaymentGateway;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Services\PaymentProcessingService;
use App\Domains\Payments\Services\PaymentReceiptService;
use App\Domains\Payments\Services\PaymentRefundService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payments\RefundPaymentRequest;
use App\Http\Requests\Payments\StorePaymentRequest;
use App\Http\Resources\Payments\PaymentRefundResource;
use App\Http\Resources\Payments\PaymentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentProcessingService $paymentProcessingService,
        private readonly PaymentRefundService $paymentRefundService,
        private readonly PaymentReceiptService $paymentReceiptService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Payment::query()->with(['invoice', 'client', 'refunds.refundedBy']);

        if ($request->filled('status')) {
            $status = (string) $request->input('status');
            $statusValues = array_map(static fn (PaymentStatus $paymentStatus): string => $paymentStatus->value, PaymentStatus::cases());

            if (in_array($status, $statusValues, true)) {
                $query->byStatus(PaymentStatus::from($status));
            }
        }

        if ($request->filled('client_id')) {
            $query->byClient((int) $request->integer('client_id'));
        }

        if ($request->filled('invoice_id')) {
            $query->byInvoice((int) $request->integer('invoice_id'));
        }

        if ($request->boolean('online_only')) {
            $query->online();
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->byDateRange(
                new \DateTime((string) $request->input('start_date')),
                new \DateTime((string) $request->input('end_date')),
            );
        }

        $payments = $query->latest('paid_at')->paginate((int) $request->integer('per_page', 25));

        return \response()->json(PaymentResource::collection($payments));
    }

    public function store(StorePaymentRequest $request): JsonResponse
    {
        $this->authorize('create', Payment::class);

        $data = PaymentData::fromRequest($request);
        $invoice = Invoice::query()->findOrFail($data->invoiceId);

        $payment = Payment::query()->create([
            'organization_id' => (int) $request->user()->organization_id,
            'client_id' => (int) $invoice->client_id,
            'invoice_id' => (int) $invoice->id,
            'amount' => $data->amount,
            'currency_code' => (string) $invoice->currency_code,
            'method' => $data->method,
            'status' => PaymentStatus::Pending,
            'gateway' => $data->method->isOnline() ? PaymentGateway::Stripe : PaymentGateway::Manual,
            'gateway_fee_amount' => 0,
            'net_amount' => $data->amount,
            'refund_amount' => 0,
            'notes' => $data->notes,
            'reference_number' => $request->filled('reference_number') ? (string) $request->input('reference_number') : null,
            'metadata' => $data->metadata,
            'created_by_user_id' => $request->user()->id,
            'paid_at' => null,
        ]);

        $result = $this->paymentProcessingService->processPayment($payment, [
            'payment_method_id' => $data->paymentMethodId,
        ]);

        if (! $result->success) {
            return \response()->json([
                'message' => $result->errorMessage,
            ], 422);
        }

        return \response()->json(PaymentResource::make($payment->fresh(['invoice', 'client', 'refunds.refundedBy'])), 201);
    }

    public function show(Payment $payment): JsonResponse
    {
        $this->authorize('view', $payment);

        return \response()->json(
            PaymentResource::make($payment->load(['invoice', 'client', 'refunds.refundedBy'])),
        );
    }

    public function refund(RefundPaymentRequest $request, Payment $payment): JsonResponse
    {
        $this->authorize('refund', $payment);

        $refund = $this->paymentRefundService->refundPayment(
            payment: $payment,
            amount: (int) $request->integer('amount'),
            reason: (string) $request->input('reason'),
        );

        return \response()->json(PaymentRefundResource::make($refund), 201);
    }

    public function receipt(Payment $payment): BinaryFileResponse
    {
        $this->authorize('viewReceipt', $payment);

        $receiptPath = $this->paymentReceiptService->generateReceipt($payment->load(['invoice', 'client']));

        return \response()->download(storage_path('app/'.$receiptPath));
    }
}
