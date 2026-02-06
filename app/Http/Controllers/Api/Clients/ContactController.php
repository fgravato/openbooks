<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Clients;

use App\Domains\Clients\Models\Client;
use App\Domains\Clients\Models\Contact;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\StoreContactRequest;
use App\Http\Requests\Clients\UpdateContactRequest;
use App\Http\Resources\Clients\ContactResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

/**
 * @group Contacts
 */
class ContactController extends Controller
{
    /**
     * List client contacts
     */
    public function index(Client $client): AnonymousResourceCollection
    {
        return ContactResource::collection($client->contacts);
    }

    /**
     * Create contact
     */
    public function store(StoreContactRequest $request, Client $client): ContactResource
    {
        $contact = $client->contacts()->create($request->validated());

        if ($request->boolean('is_primary')) {
            $this->setPrimary($contact);
        }

        return new ContactResource($contact);
    }

    /**
     * Show contact
     */
    public function show(Contact $contact): ContactResource
    {
        return new ContactResource($contact);
    }

    /**
     * Update contact
     */
    public function update(UpdateContactRequest $request, Contact $contact): ContactResource
    {
        $contact->update($request->validated());

        if ($request->boolean('is_primary')) {
            $this->setPrimary($contact);
        }

        return new ContactResource($contact);
    }

    /**
     * Delete contact
     */
    public function destroy(Contact $contact): JsonResponse
    {
        $contact->delete();

        return response()->json(null, 204);
    }

    /**
     * Set as primary contact
     */
    public function setPrimary(Contact $contact): ContactResource
    {
        DB::transaction(function () use ($contact) {
            $contact->client->contacts()->where('id', '!=', $contact->id)->update(['is_primary' => false]);
            $contact->update(['is_primary' => true]);
        });

        return new ContactResource($contact);
    }
}
