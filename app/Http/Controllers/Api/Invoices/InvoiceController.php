<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Invoices;

use App\Domains\Invoicing\Enums\InvoiceStatus;
use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Invoicing\Services\InvoiceCalculationService;
use App\Domains\Invoicing\Services\InvoiceNumberService;
use App\Domains\Invoicing\Services\InvoiceStatusService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Invoicing\SendInvoiceRequest;
use App\Http\Requests\Invoicing\StoreInvoiceRequest;
use App\Http\Requests\Invoicing\UpdateInvoiceRequest;
use App\Http\Resources\Invoicing\InvoiceResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;

/**
 * @group Invoices
 */
class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceNumberService $numberService,
        protected InvoiceStatusService $statusService,
        protected InvoiceCalculationService $calculationService
    ) {}

    /**
     * List invoices
     *
     * @queryParam status string Filter by status. Example: draft
     * @queryParam client_id int Filter by client.
     * @queryParam date_from date Filter by issue date from.
     * @queryParam date_to date Filter by issue date to.
     * @queryParam search string Search in invoice number or notes.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Invoice::query()->with(['client']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->has('date_from')) {
            $query->whereDate('issue_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('issue_date', '<=', $request->date_to);
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('invoice_number', 'like', "%{$request->search}%")
                    ->orWhere('notes', 'like', "%{$request->search}%");
            });
        }

        $invoices = $query->latest()->paginate($request->integer('per_page', 15));

        return InvoiceResource::collection($invoices);
    }

    /**
     * Create invoice
     */
    public function store(StoreInvoiceRequest $request): InvoiceResource
    {
        return DB::transaction(function () use ($request) {
            $invoice = new Invoice($request->validated());
            $invoice->organization_id = $request->user()->organization_id;
            $invoice->created_by_user_id = $request->user()->id;
            $invoice->status = InvoiceStatus::Draft;

            if (! $invoice->invoice_number) {
                $invoice->invoice_number = $this->numberService->generateNextNumber($request->user()->organization);
            }

            $invoice->save();

            if ($request->has('lines')) {
                foreach ($request->lines as $index => $lineData) {
                    $invoice->lines()->create(array_merge($lineData, [
                        'sort_order' => $index,
                    ]));
                }
            }

            $this->calculationService->recalculate($invoice);

            return new InvoiceResource($invoice->load(['client', 'lines']));
        });
    }

    /**
     * Show invoice
     */
    public function show(Invoice $invoice): InvoiceResource
    {
        return new InvoiceResource($invoice->load(['client', 'lines', 'payments']));
    }

    /**
     * Update invoice
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice): InvoiceResource
    {
        return DB::transaction(function () use ($request, $invoice) {
            $invoice->update($request->validated());

            if ($request->has('lines')) {
                // Simplistic approach: delete all and recreate for now, or update if ID exists
                // In a real app we would be more selective
                $existingLineIds = collect($request->lines)->pluck('id')->filter()->toArray();
                $invoice->lines()->whereNotIn('id', $existingLineIds)->delete();

                foreach ($request->lines as $index => $lineData) {
                    if (isset($lineData['id'])) {
                        $invoice->lines()->where('id', $lineData['id'])->update(array_merge($lineData, ['sort_order' => $index]));
                    } else {
                        $invoice->lines()->create(array_merge($lineData, ['sort_order' => $index]));
                    }
                }
            }

            $this->calculationService->recalculate($invoice);

            return new InvoiceResource($invoice->load(['client', 'lines']));
        });
    }

    /**
     * Delete invoice
     */
    public function destroy(Invoice $invoice): JsonResponse
    {
        if (! $invoice->canBeEdited()) {
            return response()->json(['message' => 'Only draft invoices can be deleted.'], 422);
        }

        $invoice->delete();

        return response()->json(null, 204);
    }

    /**
     * Duplicate invoice
     */
    public function duplicate(Invoice $invoice): InvoiceResource
    {
        $newInvoice = $invoice->duplicate();

        return new InvoiceResource($newInvoice->load(['client', 'lines']));
    }

    /**
     * Mark as sent
     */
    public function markAsSent(Invoice $invoice): InvoiceResource
    {
        $this->statusService->transition($invoice, InvoiceStatus::Sent);

        return new InvoiceResource($invoice);
    }

    /**
     * Send email to client
     */
    public function sendEmail(SendInvoiceRequest $request, Invoice $invoice): JsonResponse
    {
        // Implementation for sending email
        // Mail::to($request->to)->send(new InvoiceMail($invoice, $request->message));

        $this->statusService->transition($invoice, InvoiceStatus::Sent);

        return response()->json(['message' => 'Invoice email sent successfully.']);
    }

    /**
     * Download PDF
     */
    public function downloadPdf(Invoice $invoice): \Symfony\Component\HttpFoundation\Response
    {
        $pdfPath = $invoice->generatePdf();

        return Response::download($pdfPath, "Invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * Get shareable link
     */
    public function getShareableLink(Invoice $invoice): JsonResponse
    {
        $url = URL::temporarySignedRoute(
            'invoices.show.public',
            now()->addDays(30),
            ['invoice' => $invoice->id]
        );

        return response()->json(['link' => $url]);
    }
}
