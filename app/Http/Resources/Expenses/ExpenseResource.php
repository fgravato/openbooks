<?php

declare(strict_types=1);

namespace App\Http\Resources\Expenses;

use App\Http\Resources\Clients\ClientResource;
use App\Http\Resources\Identity\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $vendor
 * @property string|null $description
 * @property int $amount
 * @property int $tax_amount
 * @property \Carbon\Carbon $date
 * @property \App\Domains\Expenses\Enums\ExpenseStatus $status
 * @property bool $is_billable
 * @property bool $is_reimbursable
 * @property float|string|null $markup_percent
 * @property string|null $receipt_path
 * @property \Carbon\Carbon $created_at
 */
class ExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vendor' => $this->vendor,
            'description' => $this->description,
            'amount' => $this->amount,
            'tax_amount' => $this->tax_amount,
            'total' => $this->getTotalAmount(),
            'date' => $this->date->toDateString(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'is_billable' => $this->is_billable,
            'is_reimbursable' => $this->is_reimbursable,
            'markup_percent' => (float) $this->markup_percent,
            'billable_amount' => $this->getBillableAmount(),
            'receipt_url' => $this->receipt_path ? Storage::url($this->receipt_path) : null,
            'receipt_thumbnail' => $this->receipt_path ? Storage::url($this->receipt_path) : null, // Assuming same for now
            'can_edit' => $this->canBeEdited(),
            'can_approve' => $this->status->canTransitionTo(\App\Domains\Expenses\Enums\ExpenseStatus::Approved),
            'category' => new ExpenseCategoryResource($this->whenLoaded('category')),
            'client' => new ClientResource($this->whenLoaded('client')),
            'project' => new JsonResource($this->whenLoaded('project')), // Placeholder
            'approved_by' => new UserResource($this->whenLoaded('approvedBy')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
