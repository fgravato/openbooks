<?php

declare(strict_types=1);

namespace App\Traits;

use App\Domains\Identity\Models\Organization;
use App\Scopes\OrganizationScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToOrganization
{
    public function initializeBelongsToOrganization(): void
    {
        $this->fillable = array_values(array_unique([
            ...$this->fillable,
            'organization_id',
        ]));
    }

    public static function bootBelongsToOrganization(): void
    {
        static::addGlobalScope(new OrganizationScope());

        static::creating(static function (Model $model): void {
            if ($model->getAttribute('organization_id') !== null) {
                return;
            }

            $organizationId = OrganizationScope::resolveCurrentOrganizationId();

            if ($organizationId !== null) {
                $model->setAttribute('organization_id', $organizationId);
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function scopeToCurrentOrganization(Builder $query): Builder
    {
        $organizationId = OrganizationScope::resolveCurrentOrganizationId();

        if ($organizationId === null) {
            return $query;
        }

        return $query->where($query->getModel()->qualifyColumn('organization_id'), $organizationId);
    }
}
