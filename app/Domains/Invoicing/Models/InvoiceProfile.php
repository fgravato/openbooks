<?php

declare(strict_types=1);

namespace App\Domains\Invoicing\Models;

use App\Domains\Clients\Models\Client;
use App\Domains\Identity\Models\Organization;
use App\Domains\Invoicing\Enums\DiscountType;
use App\Domains\Invoicing\Enums\InvoiceLineType;
use App\Domains\Invoicing\Enums\InvoiceProfileFrequency;
use App\Domains\Invoicing\Enums\InvoiceStatus;
use App\Domains\Invoicing\Services\InvoiceNumberService;
use App\Traits\BelongsToOrganization;
use Carbon\Carbon;
use Database\Factories\Invoicing\InvoiceProfileFactory;
use DateTime;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceProfile extends Model
{
    use BelongsToOrganization;
    use HasFactory;

    protected $table = 'invoice_profiles';

    protected $fillable = [
        'organization_id',
        'client_id',
        'name',
        'frequency',
        'custom_days',
        'next_issue_date',
        'end_date',
        'occurrences_remaining',
        'auto_send',
        'template_data',
        'is_active',
        'last_generated_at',
    ];

    protected function casts(): array
    {
        return [
            'frequency' => InvoiceProfileFrequency::class,
            'next_issue_date' => 'date',
            'end_date' => 'date',
            'last_generated_at' => 'date',
            'template_data' => 'array',
            'auto_send' => 'boolean',
            'is_active' => 'boolean',
            'custom_days' => 'integer',
            'occurrences_remaining' => 'integer',
        ];
    }

    protected static function newFactory(): Factory
    {
        return InvoiceProfileFactory::new();
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function generatedInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'invoice_profile_id');
    }

    public function shouldGenerate(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->next_issue_date === null || $this->next_issue_date->isFuture()) {
            return false;
        }

        if ($this->end_date !== null && $this->end_date->isPast()) {
            return false;
        }

        return $this->occurrences_remaining === null || $this->occurrences_remaining > 0;
    }

    public function generateInvoice(): Invoice
    {
        $templateData = (array) $this->template_data;

        $invoice = new Invoice;
        $invoice->organization_id = (int) $this->organization_id;
        $invoice->client_id = (int) $this->client_id;
        $invoice->invoice_profile_id = (int) $this->id;
        $invoice->invoice_number = app(InvoiceNumberService::class)->generateNextNumber($this->organization);
        $invoice->status = InvoiceStatus::Draft;
        $invoice->issue_date = Carbon::today();
        $invoice->due_date = Carbon::today()->addDays((int) ($templateData['payment_terms_days'] ?? 30));
        $invoice->currency_code = (string) ($templateData['currency_code'] ?? $this->organization->currency_code ?? 'USD');
        $invoice->discount_type = isset($templateData['discount_type'])
            ? DiscountType::from((string) $templateData['discount_type'])
            : null;
        $invoice->discount_value = (int) ($templateData['discount_value'] ?? 0);
        $invoice->notes = $templateData['notes'] ?? null;
        $invoice->terms = $templateData['terms'] ?? null;
        $invoice->template = $templateData['template'] ?? 'default';
        $invoice->footer_text = $templateData['footer_text'] ?? null;
        $invoice->subtotal = 0;
        $invoice->tax_amount = 0;
        $invoice->total = 0;
        $invoice->amount_paid = 0;
        $invoice->amount_outstanding = 0;
        $invoice->save();

        $lines = $templateData['lines'] ?? [];
        foreach ($lines as $index => $lineData) {
            $line = new InvoiceLine;
            $line->invoice_id = $invoice->id;
            $line->type = InvoiceLineType::from((string) ($lineData['type'] ?? InvoiceLineType::Item->value));
            $line->description = (string) ($lineData['description'] ?? 'Service');
            $line->quantity = (float) ($lineData['quantity'] ?? 1);
            $line->unit_price = (int) ($lineData['unit_price'] ?? 0);
            $line->tax_name = $lineData['tax_name'] ?? null;
            $line->tax_percent = isset($lineData['tax_percent']) ? (float) $lineData['tax_percent'] : null;
            $line->expense_id = isset($lineData['expense_id']) ? (int) $lineData['expense_id'] : null;
            $line->time_entry_id = isset($lineData['time_entry_id']) ? (int) $lineData['time_entry_id'] : null;
            $line->sort_order = $index + 1;
            $line->save();
        }

        $invoice->calculateTotals();

        $this->next_issue_date = Carbon::instance($this->calculateNextDate());
        $this->last_generated_at = Carbon::today();

        if ($this->occurrences_remaining !== null) {
            $this->occurrences_remaining = max(0, $this->occurrences_remaining - 1);
        }

        $this->save();

        return $invoice->fresh();
    }

    public function calculateNextDate(): DateTime
    {
        $base = Carbon::parse((string) $this->next_issue_date);

        $next = match ($this->frequency) {
            InvoiceProfileFrequency::Weekly => $base->copy()->addWeek(),
            InvoiceProfileFrequency::BiWeekly => $base->copy()->addWeeks(2),
            InvoiceProfileFrequency::Monthly => $base->copy()->addMonth(),
            InvoiceProfileFrequency::Quarterly => $base->copy()->addMonths(3),
            InvoiceProfileFrequency::Annually => $base->copy()->addYear(),
            InvoiceProfileFrequency::Custom => $base->copy()->addDays(max(1, (int) $this->custom_days)),
        };

        return $next->toDateTime();
    }
}
