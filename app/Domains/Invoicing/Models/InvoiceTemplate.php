<?php

declare(strict_types=1);

namespace App\Domains\Invoicing\Models;

use App\Domains\Identity\Models\Organization;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceTemplate extends Model
{
    use BelongsToOrganization;
    use HasFactory;

    protected $table = 'invoice_templates';

    protected $fillable = [
        'organization_id',
        'name',
        'is_default',
        'header_html',
        'footer_html',
        'css_styles',
        'logo_position',
        'color_primary',
        'color_secondary',
        'paper_size',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function render(Invoice $invoice): string
    {
        $replacements = [
            '{{invoice_number}}' => (string) $invoice->invoice_number,
            '{{issue_date}}' => (string) $invoice->issue_date?->format('Y-m-d'),
            '{{due_date}}' => (string) $invoice->due_date?->format('Y-m-d'),
            '{{client_name}}' => (string) ($invoice->client?->company_name ?: $invoice->client?->first_name),
            '{{subtotal}}' => number_format(((int) $invoice->subtotal) / 100, 2),
            '{{tax_amount}}' => number_format(((int) $invoice->tax_amount) / 100, 2),
            '{{total}}' => number_format(((int) $invoice->total) / 100, 2),
            '{{amount_outstanding}}' => number_format(((int) $invoice->amount_outstanding) / 100, 2),
            '{{notes}}' => (string) ($invoice->notes ?? ''),
            '{{terms}}' => (string) ($invoice->terms ?? ''),
        ];

        $header = strtr((string) ($this->header_html ?? ''), $replacements);
        $footer = strtr((string) ($this->footer_html ?? ''), $replacements);

        return <<<HTML
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <style>{$this->css_styles}</style>
</head>
<body>
  <header>{$header}</header>
  <main>
    <h1>Invoice {$invoice->invoice_number}</h1>
    <p>Total: {$replacements['{{total}}']} {$invoice->currency_code}</p>
  </main>
  <footer>{$footer}</footer>
</body>
</html>
HTML;
    }
}
