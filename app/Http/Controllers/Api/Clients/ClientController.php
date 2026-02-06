<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Clients;

use App\Domains\Clients\Models\Client;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\StoreClientRequest;
use App\Http\Requests\Clients\UpdateClientRequest;
use App\Http\Resources\Clients\ClientResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

/**
 * @group Clients
 */
class ClientController extends Controller
{
    /**
     * List clients
     *
     * @queryParam search string Search in name, company or email.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Client::query()
            ->withCount(['invoices', 'contacts']);

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
                  ->orWhere('company_name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        // Add subqueries for totals if needed, or handle in resource with counts
        $query->select('*')
            ->addSelect([
                'total_invoiced' => DB::table('invoices')
                    ->whereColumn('client_id', 'clients.id')
                    ->whereNull('deleted_at')
                    ->selectRaw('SUM(total)'),
                'total_paid' => DB::table('invoices')
                    ->whereColumn('client_id', 'clients.id')
                    ->whereNull('deleted_at')
                    ->selectRaw('SUM(amount_paid)'),
                'balance_outstanding' => DB::table('invoices')
                    ->whereColumn('client_id', 'clients.id')
                    ->whereNull('deleted_at')
                    ->selectRaw('SUM(amount_outstanding)'),
            ]);

        $clients = $query->latest()->paginate($request->integer('per_page', 15));

        return ClientResource::collection($clients);
    }

    /**
     * Create client
     */
    public function store(StoreClientRequest $request): ClientResource
    {
        $client = new Client($request->validated());
        $client->organization_id = $request->user()->organization_id;
        $client->save();

        return new ClientResource($client);
    }

    /**
     * Show client
     */
    public function show(Client $client): ClientResource
    {
        $client->loadCount(['invoices', 'contacts']);
        
        // Add stats manually if not using subqueries in show
        $client->total_invoiced = $client->invoices()->sum('total');
        $client->total_paid = $client->invoices()->sum('amount_paid');
        $client->balance_outstanding = $client->invoices()->sum('amount_outstanding');

        return new ClientResource($client->load(['contacts']));
    }

    /**
     * Update client
     */
    public function update(UpdateClientRequest $request, Client $client): ClientResource
    {
        $client->update($request->validated());

        return new ClientResource($client);
    }

    /**
     * Delete client
     */
    public function destroy(Client $client): JsonResponse
    {
        if ($client->invoices()->exists()) {
            return response()->json(['message' => 'Cannot delete client with invoices.'], 422);
        }

        $client->delete();

        return response()->json(null, 204);
    }

    /**
     * Generate account statement
     */
    public function getStatement(Request $request, Client $client): JsonResponse
    {
        // Implementation for statement generation
        return response()->json([
            'client' => new ClientResource($client),
            'statement_url' => '...',
        ]);
    }
}
