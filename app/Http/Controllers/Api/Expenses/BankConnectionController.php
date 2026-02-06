<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Expenses;

use App\Domains\Expenses\Models\BankConnection;
use App\Domains\Expenses\Services\BankSyncService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Expenses\StoreBankConnectionRequest;
use App\Http\Requests\Expenses\UpdateBankConnectionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
    public function index(): JsonResource
    {
        $connections = BankConnection::all();
        return JsonResource::collection($connections);
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
            'access_token' => $tokenData['access_token'],
            'item_id' => $tokenData['item_id'],
            'is_active' => true,
        ]);

        return response()->json(new JsonResource($connection), 201);
    }

    /**
     * Show bank connection
     */
    public function show(BankConnection $connection): JsonResource
    {
        return new JsonResource($connection);
    }

    /**
     * Update bank connection
     */
    public function update(UpdateBankConnectionRequest $request, BankConnection $connection): JsonResource
    {
        $connection->update($request->validated());
        return new JsonResource($connection);
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
