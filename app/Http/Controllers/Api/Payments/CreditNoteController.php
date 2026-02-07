<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Payments;

use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Payments\DTOs\CreditNoteData;
use App\Domains\Payments\Models\CreditNote;
use App\Domains\Payments\Services\CreditNoteService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payments\ApplyCreditNoteRequest;
use App\Http\Requests\Payments\StoreCreditNoteRequest;
use App\Http\Resources\Payments\CreditNoteResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreditNoteController extends Controller
{
    public function __construct(private readonly CreditNoteService $creditNoteService) {}

    public function index(Request $request): JsonResponse
    {
        $creditNotes = CreditNote::query()
            ->with(['client', 'invoice', 'appliedTo'])
            ->latest()
            ->paginate((int) $request->integer('per_page', 25));

        return \response()->json(CreditNoteResource::collection($creditNotes));
    }

    public function store(StoreCreditNoteRequest $request): JsonResponse
    {
        $creditNote = $this->creditNoteService->createCreditNote(new CreditNoteData(
            clientId: (int) $request->integer('client_id'),
            amount: (int) $request->integer('amount'),
            reason: (string) $request->input('reason'),
            invoiceId: $request->filled('invoice_id') ? (int) $request->integer('invoice_id') : null,
        ));

        return \response()->json(
            CreditNoteResource::make($creditNote->load(['client', 'invoice', 'appliedTo'])),
            201,
        );
    }

    public function show(CreditNote $creditNote): JsonResponse
    {
        return \response()->json(CreditNoteResource::make($creditNote->load(['client', 'invoice', 'appliedTo'])));
    }

    public function apply(ApplyCreditNoteRequest $request, CreditNote $creditNote): JsonResponse
    {
        $invoice = Invoice::query()->findOrFail((int) $request->integer('invoice_id'));

        $this->creditNoteService->applyCreditNote(
            creditNote: $creditNote,
            invoice: $invoice,
            amount: $request->filled('amount') ? (int) $request->integer('amount') : null,
        );

        return \response()->json(CreditNoteResource::make($creditNote->fresh(['client', 'invoice', 'appliedTo'])));
    }

    public function void(CreditNote $creditNote): JsonResponse
    {
        $this->creditNoteService->voidCreditNote($creditNote);

        return \response()->json(CreditNoteResource::make($creditNote->fresh(['client', 'invoice', 'appliedTo'])));
    }
}
