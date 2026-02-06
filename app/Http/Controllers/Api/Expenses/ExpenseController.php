<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Expenses;

use App\Domains\Expenses\Enums\ExpenseStatus;
use App\Domains\Expenses\Models\Expense;
use App\Domains\Expenses\Services\ExpenseApprovalService;
use App\Domains\Expenses\Services\ExpenseCalculationService;
use App\Domains\Expenses\Services\ReceiptProcessingService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Expenses\StoreExpenseRequest;
use App\Http\Requests\Expenses\UpdateExpenseRequest;
use App\Http\Requests\Expenses\UploadReceiptRequest;
use App\Http\Resources\Expenses\ExpenseResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

/**
 * @group Expenses
 */
class ExpenseController extends Controller
{
    public function __construct(
        protected ExpenseApprovalService $approvalService,
        protected ExpenseCalculationService $calculationService,
        protected ReceiptProcessingService $receiptProcessingService,
    ) {}

    /**
     * List expenses
     *
     * @queryParam status string Filter by status.
     * @queryParam category_id int Filter by category.
     * @queryParam client_id int Filter by client.
     * @queryParam date_from date.
     * @queryParam date_to date.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Expense::query()->with(['category', 'client', 'project', 'approvedBy']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->has('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $expenses = $query->latest('date')->paginate($request->integer('per_page', 15));

        return ExpenseResource::collection($expenses);
    }

    /**
     * Create expense
     */
    public function store(StoreExpenseRequest $request): ExpenseResource
    {
        $validated = $request->validated();
        $taxPercent = isset($validated['tax_percent']) ? (float) $validated['tax_percent'] : 0;
        $validated['tax_amount'] = $this->calculationService->calculateTaxAmount((int) $validated['amount'], $taxPercent);

        $expense = new Expense($validated);
        $expense->organization_id = $request->user()->organization_id;
        $expense->user_id = $request->user()->id;
        $expense->status = ExpenseStatus::Pending;
        $expense->save();

        return new ExpenseResource($expense->load(['category', 'client']));
    }

    /**
     * Show expense
     */
    public function show(Expense $expense): ExpenseResource
    {
        return new ExpenseResource($expense->load(['category', 'client', 'project', 'approvedBy']));
    }

    /**
     * Update expense
     */
    public function update(UpdateExpenseRequest $request, Expense $expense): ExpenseResource|JsonResponse
    {
        if (! $expense->canBeEdited()) {
            return response()->json(['message' => 'Only pending expenses can be edited.'], 422);
        }

        $validated = $request->validated();

        if (array_key_exists('amount', $validated) || array_key_exists('tax_percent', $validated)) {
            $amount = array_key_exists('amount', $validated) ? (int) $validated['amount'] : (int) $expense->amount;
            $taxPercent = array_key_exists('tax_percent', $validated) ? (float) $validated['tax_percent'] : (float) ($expense->tax_percent ?? 0);
            $validated['tax_amount'] = $this->calculationService->calculateTaxAmount($amount, $taxPercent);
        }

        $expense->update($validated);

        return new ExpenseResource($expense->load(['category', 'client']));
    }

    /**
     * Delete expense
     */
    public function destroy(Expense $expense): JsonResponse
    {
        $expense->delete();

        return response()->json(null, 204);
    }

    /**
     * Submit for approval
     */
    public function submitForApproval(Expense $expense): ExpenseResource
    {
        $this->approvalService->submitForApproval($expense);

        return new ExpenseResource($expense);
    }

    /**
     * Approve expense
     */
    public function approve(Expense $expense, Request $request): ExpenseResource
    {
        $this->approvalService->approve($expense, $request->user());

        return new ExpenseResource($expense);
    }

    /**
     * Reject expense
     */
    public function reject(Request $request, Expense $expense): ExpenseResource
    {
        $request->validate(['reason' => ['required', 'string']]);
        
        $this->approvalService->reject($expense, $request->reason);

        return new ExpenseResource($expense);
    }

    /**
     * Upload receipt
     */
    public function attachReceipt(UploadReceiptRequest $request, Expense $expense): ExpenseResource
    {
        $file = $request->file('file') ?? $request->file('receipt');

        if ($file === null) {
            abort(422, 'A receipt file is required.');
        }

        $this->receiptProcessingService->uploadReceipt($expense, $file);

        return new ExpenseResource($expense->fresh(['category', 'client', 'project', 'approvedBy']));
    }

    public function uploadReceipt(UploadReceiptRequest $request, Expense $expense): ExpenseResource
    {
        return $this->attachReceipt($request, $expense);
    }

    /**
     * Remove receipt
     */
    public function removeReceipt(Expense $expense): JsonResponse
    {
        $this->receiptProcessingService->deleteReceipt($expense);

        return response()->json(null, 204);
    }

    /**
     * Download receipt
     */
    public function downloadReceipt(Expense $expense): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        if (! $expense->receipt_path) {
            abort(404);
        }

        return Storage::download($expense->receipt_path);
    }
}
