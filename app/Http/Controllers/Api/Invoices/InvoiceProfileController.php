<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Invoices;

use App\Domains\Invoicing\Models\InvoiceProfile;
use App\Domains\Invoicing\Services\RecurringInvoiceService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Invoicing\StoreInvoiceProfileRequest;
use App\Http\Requests\Invoicing\UpdateInvoiceProfileRequest;
use App\Http\Resources\Invoicing\InvoiceProfileResource;
use App\Http\Resources\Invoicing\InvoiceResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Invoice Profiles
 */
class InvoiceProfileController extends Controller
{
    public function __construct(
        protected RecurringInvoiceService $recurringService
    ) {}

    /**
     * List invoice profiles
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $profiles = InvoiceProfile::query()
            ->with(['client'])
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return InvoiceProfileResource::collection($profiles);
    }

    /**
     * Create invoice profile
     */
    public function store(StoreInvoiceProfileRequest $request): InvoiceProfileResource
    {
        $profile = new InvoiceProfile($request->validated());
        $profile->organization_id = $request->user()->organization_id;
        $profile->is_active = true;
        $profile->save();

        return new InvoiceProfileResource($profile->load('client'));
    }

    /**
     * Show invoice profile
     */
    public function show(InvoiceProfile $profile): InvoiceProfileResource
    {
        return new InvoiceProfileResource($profile->load('client'));
    }

    /**
     * Update invoice profile
     */
    public function update(UpdateInvoiceProfileRequest $request, InvoiceProfile $profile): InvoiceProfileResource
    {
        $profile->update($request->validated());

        return new InvoiceProfileResource($profile->load('client'));
    }

    /**
     * Delete invoice profile
     */
    public function destroy(InvoiceProfile $profile): JsonResponse
    {
        $profile->delete();

        return response()->json(null, 204);
    }

    /**
     * Pause generating invoices
     */
    public function pause(InvoiceProfile $profile): InvoiceProfileResource
    {
        $profile->update(['is_active' => false]);

        return new InvoiceProfileResource($profile);
    }

    /**
     * Resume generating invoices
     */
    public function resume(InvoiceProfile $profile): InvoiceProfileResource
    {
        $profile->update(['is_active' => true]);

        return new InvoiceProfileResource($profile);
    }

    /**
     * Manually generate invoice now
     */
    public function generateNow(InvoiceProfile $profile): InvoiceResource
    {
        $invoice = $profile->generateInvoice();

        return new InvoiceResource($invoice);
    }
}
