<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Models;

use App\Domains\Identity\Models\Organization;
use App\Traits\BelongsToOrganization;
use Database\Factories\Expenses\ExpenseCategoryFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model
{
    use BelongsToOrganization;
    use HasFactory;

    protected string $table = 'expense_categories';

    protected array $fillable = [
        'organization_id',
        'name',
        'description',
        'parent_id',
        'color',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    protected static function newFactory(): Factory
    {
        return ExpenseCategoryFactory::new();
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'category_id');
    }

    public function getFullName(): string
    {
        if ($this->parent === null) {
            return (string) $this->name;
        }

        return sprintf('%s > %s', $this->parent->name, $this->name);
    }
}
