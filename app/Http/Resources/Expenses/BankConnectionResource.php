<?php

declare(strict_types=1);

namespace App\Http\Resources\Expenses;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankConnectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'institution_name' => $this->institution_name,
            'account_mask' => $this->account_mask,
            'account_type' => $this->account_type?->value,
            'balance_current' => (int) $this->balance_current,
            'last_sync_at' => $this->last_sync_at?->toIso8601String(),
            'is_active' => (bool) $this->is_active,
        ];
    }
}
