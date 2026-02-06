# AGENTS.md - OpenBooks Development Guide

> Cloud-based invoicing & accounting platform built with PHP 8.5 + Laravel 12

## Quick Reference

| Task | Command |
|------|---------|
| Install dependencies | `composer install && npm install` |
| Start dev server | `php artisan serve` / `npm run dev` |
| Run all tests | `php artisan test` |
| Run single test | `php artisan test --filter=TestClassName` |
| Run single test file | `php artisan test tests/Feature/InvoiceTest.php` |
| Run Pest tests | `./vendor/bin/pest` |
| Run single Pest test | `./vendor/bin/pest --filter="test name"` |
| Static analysis | `./vendor/bin/phpstan analyse` |
| Code formatting | `./vendor/bin/pint` |
| Check formatting | `./vendor/bin/pint --test` |
| Build assets | `npm run build` |
| Fresh migrations | `php artisan migrate:fresh --seed` |
| Generate IDE helpers | `php artisan ide-helper:generate` |

---

## Architecture Overview

### Domain-Driven Design (DDD)
The codebase follows DDD with bounded contexts. Each domain is self-contained:

```
app/
├── Domains/
│   ├── Identity/          # Auth, users, roles, permissions
│   ├── Invoicing/         # Invoices, line items, recurring profiles
│   ├── Payments/          # Payment recording, gateways, refunds
│   ├── Expenses/          # Expense tracking, receipts, bank imports
│   ├── TimeTracking/      # Timers, entries, services
│   ├── Projects/          # Project management, budgets, tasks
│   ├── Clients/           # Client profiles, contacts, portal
│   ├── Accounting/        # Chart of accounts, journal entries
│   ├── Estimates/         # Estimates, proposals
│   └── Reporting/         # Financial reports
```

Each domain contains: `Models/`, `Services/`, `Repositories/`, `Events/`, `Policies/`, `Actions/`, `DTOs/`

### Multi-Tenancy
- Single database with `organization_id` scoping on ALL tenant entities
- Global scopes auto-filter queries to current tenant
- Tenant resolved via subdomain OR API token
- **ALWAYS** include `organization_id` in queries and foreign keys

---

## Code Style Guidelines

### PHP 8.5 Requirements
Use modern PHP features throughout:

```php
// Enums for statuses (REQUIRED for all status fields)
enum InvoiceStatus: string {
    case Draft = 'draft';
    case Sent = 'sent';
    case Viewed = 'viewed';
    case Partial = 'partial';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Cancelled = 'cancelled';
}

// Readonly classes for value objects
readonly class Money {
    public function __construct(
        public int $cents,
        public string $currency,
    ) {}
}

// Typed properties everywhere (never use mixed/untyped)
public string $name;
public ?Client $client = null;

// Match expressions for status logic
$label = match($invoice->status) {
    InvoiceStatus::Draft => 'Draft',
    InvoiceStatus::Sent, InvoiceStatus::Viewed => 'Awaiting Payment',
    InvoiceStatus::Paid => 'Paid',
    default => 'Unknown',
};
```

### Naming Conventions
| Type | Convention | Example |
|------|------------|---------|
| Classes | PascalCase | `InvoiceService`, `PaymentGateway` |
| Methods | camelCase | `calculateTotal()`, `sendToClient()` |
| Variables | camelCase | `$invoiceLines`, `$taxAmount` |
| Constants | SCREAMING_SNAKE | `MAX_RETRY_ATTEMPTS` |
| Database columns | snake_case | `organization_id`, `created_at` |
| Routes | kebab-case | `/api/v1/invoice-profiles` |
| Enums | PascalCase values | `InvoiceStatus::AwaitingPayment` |

### Import Order
```php
<?php

declare(strict_types=1);

namespace App\Domains\Invoicing\Services;

// 1. PHP core classes
use DateTimeImmutable;
use InvalidArgumentException;

// 2. Laravel/framework classes
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

// 3. Third-party packages
use Stripe\PaymentIntent;

// 4. Application classes (by domain)
use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Invoicing\DTOs\InvoiceData;
use App\Domains\Clients\Models\Client;
```

### Type Declarations
- **REQUIRED**: Return types on ALL methods
- **REQUIRED**: Parameter types on ALL parameters
- **REQUIRED**: Property types on ALL properties
- **FORBIDDEN**: `@var` docblocks for typed properties
- **FORBIDDEN**: `mixed` type (be specific)
- Use union types: `string|int`, nullable: `?string`
- Use intersection types for repositories: `ClientRepositoryInterface&Cacheable`

### PHPStan Level 8
The codebase MUST pass PHPStan Level 8. Common requirements:
- No `@phpstan-ignore` unless absolutely necessary (document why)
- No `@var` type overrides for Eloquent relationships (use proper return types)
- Handle all possible null cases explicitly
- Never use `as any` patterns with generics

---

## Laravel Conventions

### Controllers
- Single responsibility: one resource per controller
- Use Form Requests for validation
- Return Inertia responses or API Resources
- No business logic (delegate to Services/Actions)

```php
public function store(StoreInvoiceRequest $request): RedirectResponse
{
    $invoice = $this->invoiceService->create(
        InvoiceData::fromRequest($request)
    );
    
    return redirect()->route('invoices.show', $invoice);
}
```

### Models
- Always define `$fillable` (never use `$guarded = []`)
- Define relationships with return types
- Use model scopes for common queries
- Implement `BelongsToOrganization` trait for tenant scoping

```php
class Invoice extends Model
{
    use SoftDeletes, BelongsToOrganization;

    protected $fillable = ['client_id', 'invoice_number', 'status', ...];
    
    protected $casts = [
        'status' => InvoiceStatus::class,
        'due_date' => 'date',
        'total' => 'integer', // Store cents
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
```

### Migrations
- Descriptive names: `create_invoices_table`, `add_status_to_invoices`
- Always include `organization_id` with foreign key + index
- Use composite indexes for common query patterns
- NEVER modify existing migrations (create new ones)

---

## Frontend (Vue 3 + Inertia + Tailwind)

### Component Structure
```vue
<script setup lang="ts">
// Props and emits first
const props = defineProps<{ invoice: Invoice }>();
const emit = defineEmits<{ (e: 'updated', invoice: Invoice): void }>();

// Composables
const { formatMoney } = useCurrency();

// Reactive state
const isEditing = ref(false);
</script>

<template>
  <!-- Single root element, semantic HTML -->
</template>
```

### Tailwind Classes
- Use design tokens from `tailwind.config.js`
- Group utilities logically: layout → sizing → spacing → typography → colors
- Extract components for repeated patterns (don't repeat 10+ classes)

---

## Testing

### Test Organization
```
tests/
├── Unit/                  # Pure logic, no framework
│   └── Domains/
├── Feature/              # HTTP, database, framework
│   └── Api/
└── Browser/              # Laravel Dusk E2E
```

### Pest PHP Conventions
```php
it('calculates invoice total with taxes', function () {
    $invoice = Invoice::factory()
        ->hasLines(3, ['unit_price' => 10000]) // $100 each
        ->create();

    expect($invoice->total)->toBe(30000);
});

it('prevents unauthorized access', function () {
    $invoice = Invoice::factory()->create();
    
    actingAs(User::factory()->create()) // Different org
        ->get(route('invoices.show', $invoice))
        ->assertForbidden();
});
```

### Test Database
- Use `RefreshDatabase` trait for feature tests
- Use factories for all test data (never raw DB inserts)
- Each test creates its own organization context

---

## Error Handling

### Domain Exceptions
Each domain defines its own exceptions:
```php
namespace App\Domains\Invoicing\Exceptions;

class InvoiceCannotBePaidException extends DomainException
{
    public static function alreadyPaid(Invoice $invoice): self
    {
        return new self("Invoice #{$invoice->invoice_number} is already paid");
    }
}
```

### API Error Responses
```php
// Return proper HTTP status codes
return response()->json([
    'message' => 'Invoice not found',
    'errors' => ['invoice_id' => ['The specified invoice does not exist']],
], 404);
```

---

## Security Checklist

- [ ] All endpoints require authentication (except public invoice view)
- [ ] Organization scoping applied via global scope
- [ ] Form requests validate authorization (`authorize()` method)
- [ ] No raw SQL without parameter binding
- [ ] Stripe Elements for payment forms (no card data on server)
- [ ] Audit log for financial modifications
- [ ] Rate limiting on authentication endpoints

---

## Git Conventions

### Branch Naming
- `feature/INV-123-invoice-templates`
- `fix/PAY-456-stripe-webhook-retry`
- `refactor/expenses-domain-cleanup`

### Commit Messages
```
feat(invoicing): add recurring invoice profiles

- Implement InvoiceProfile model with frequency options
- Add scheduled command for profile processing
- Create migration for invoice_profiles table

Closes #123
```

Prefixes: `feat`, `fix`, `refactor`, `test`, `docs`, `chore`
