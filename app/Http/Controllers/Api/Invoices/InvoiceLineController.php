<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Invoices;

use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Invoicing\Models\InvoiceLine;
use App\Domains\Invoicing\Services\InvoiceCalculationService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Invoicing\StoreInvoiceLineRequest;
use App\Http\Requests\Invoicing\UpdateInvoiceLineRequest;
use App\Http\Resources\Invoicing\InvoiceLineResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Invoice Lines
 */
class InvoiceLineController extends Controller
{
    public function __construct(
        protected InvoiceCalculationService $calculationService
    ) {}

    /**
     * Add line item
     */
    public function store(StoreInvoiceLineRequest $request, Invoice $invoice): InvoiceLineResource
    {
        $line = $invoice->lines()->create($request->validated());
        
        $this->calculationService->recalculate($invoice);

        return new InvoiceLineResource($line);
    }

    /**
     * Update line item
     */
    public function update(UpdateInvoiceLineRequest $request, InvoiceLine $line): InvoiceLineResource
    {
        $line->update($request->validated());
        
        $this->calculationService->recalculate($line->invoice);

        return new InvoiceLineResource($line);
    }

    /**
     * Remove line item
     */
    public function destroy(InvoiceLine $line): JsonResponse
    {
        $invoice = $line->invoice;
        $line->delete();
        
        $this->calculationService->recalculate($invoice);

        return response()->json(null, 204);
    }

    /**
     * Reorder line items
     */
    public function reorder(Request $request, Invoice $invoice): JsonResponse
    {
        $request->validate([
            'lines' => ['required', 'array'],
            'lines.*.id' => ['required', 'exists:invoice_lines,id'],
            'lines.*.sort_order' => ['required', 'integer'],
        ]);

        foreach ($request->lines as $lineData) {
            $invoice->lines()->where('id', $lineData['id'])->update([
                'sort_order' => $lineData['sort_order'],
            ]);
        }

        return response()->json(['message' => 'Lines reordered successfully.']);
    }
}
