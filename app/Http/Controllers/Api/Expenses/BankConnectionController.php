<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Expenses;

use App\Domains\Expenses\Models\BankConnection;
use App\Domains\Expenses\Services\BankSyncService;
use App\Http\Controllers\Controller;
use App\Http\Resources\Expenses\BankConnectionResource;
use App\Http\Requests\Expenses\StoreBankConnectionRequest;
use App\Http\Requests\Expenses\UpdateBankConnectionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Bank Connections
 */
class BankConnectionController extends Controller
{
    public function __construct(
        protected BankSyncService $bankSyncService
    ) {}

    /**
     * List bank connections
     */
    public function index(): AnonymousResourceCollection
    {
        return BankConnectionResource::collection(BankConnection::query()->orderBy('name')->get());
    }

    /**
     * Create bank connection
     */
    public function store(StoreBankConnectionRequest $request): JsonResponse
    {
        $tokenData = $this->bankSyncService->exchangePublicToken($request->public_token);
        
        $connection = BankConnection::create([
            'organization_id' => $request->user()->organization_id,
            'name' => $request->name,
            'institution_name' => $request->institution_name,
            'institution_id' => $request->institution_id,
            'access_token' => $tokenData['access_token'],
            'item_id' => $tokenData['item_id'],
            'account_mask' => $request->account_mask,
            'account_type' => $request->account_type,
            'balance_current' => 0,
            'balance_available' => 0,
            'currency_code' => $request->input('currency_code', 'USD'),
            'is_active' => true,
        ]);

        return response()->json(BankConnectionResource::make($connection), 201);
    }

    /**
     * Show bank connection
     */
    public function show(BankConnection $connection): BankConnectionResource
    {
        return new BankConnectionResource($connection);
    }

    /**
     * Update bank connection
     */
    public function update(UpdateBankConnectionRequest $request, BankConnection $connection): BankConnectionResource
    {
        $connection->update($request->validated());

        return new BankConnectionResource($connection);
    }

    /**
     * Delete bank connection
     */
    public function destroy(BankConnection $connection): JsonResponse
    {
        $connection->delete();
        return response()->json(null, 204);
    }

    /**
     * Sync transactions
     */
    public function sync(BankConnection $connection): JsonResponse
    {
        $synced = $this->bankSyncService->syncTransactions($connection);
        $matched = $this->bankSyncService->matchTransactionsToExpenses($connection);

        return response()->json([
            'message' => 'Bank sync completed.',
            'synced_count' => $synced,
            'matched_count' => $matched,
        ]);
    }
}
