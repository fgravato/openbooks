<?php

declare(strict_types=1);

namespace App\Http\Resources\Clients;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $company_name
 * @property string $email
 * @property string|null $phone
 * @property array $address
 * @property string $currency_code
 * @property string $language
 * @property int|null $payment_terms
 * @property \Carbon\Carbon $created_at
 * @property int $invoices_count
 * @property int $contacts_count
 */
class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'company_name' => $this->company_name,
            'full_name' => trim("{$this->first_name} {$this->last_name}") ?: $this->company_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'currency_code' => $this->currency_code,
            'language' => $this->language,
            'payment_terms' => $this->payment_terms,
            'total_invoiced' => $this->whenHas('total_invoiced', $this->total_invoiced), // Assumes scoped or calculated in query
            'total_paid' => $this->whenHas('total_paid', $this->total_paid),
            'balance_outstanding' => $this->whenHas('balance_outstanding', $this->balance_outstanding),
            'contacts_count' => $this->contacts_count,
            'invoices_count' => $this->invoices_count,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
