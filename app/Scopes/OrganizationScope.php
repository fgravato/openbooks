<?php

declare(strict_types=1);

namespace App\Scopes;

use App\Domains\Identity\Models\Organization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Throwable;

class OrganizationScope implements Scope
{
    private static array $organizationCache = [];

    public function apply(Builder $builder, Model $model): void
    {
        if (! $this->shouldApply($model)) {
            return;
        }

        $organizationId = self::resolveCurrentOrganizationId();

        if ($organizationId === null) {
            if (! App::runningInConsole()) {
                $builder->whereRaw('1 = 0');
            }

            return;
        }

        $builder->where($model->qualifyColumn('organization_id'), $organizationId);
    }

    public static function resolveCurrentOrganizationId(): ?int
    {
        $userOrganizationId = Auth::user()?->organization_id;

        if (is_int($userOrganizationId)) {
            return $userOrganizationId;
        }

        if (! \app()->bound('request')) {
            return null;
        }

        $host = strtolower((string) \request()->getHost());

        if ($host === '' || $host === 'localhost') {
            return null;
        }

        if (array_key_exists($host, self::$organizationCache)) {
            return self::$organizationCache[$host];
        }

        $slug = self::resolveSlugFromHost($host);

        if ($slug === null) {
            self::$organizationCache[$host] = null;

            return null;
        }

        $organizationId = Organization::query()
            ->withoutGlobalScopes()
            ->where('slug', $slug)
            ->value('id');

        self::$organizationCache[$host] = is_int($organizationId) ? $organizationId : null;

        return self::$organizationCache[$host];
    }

    private function shouldApply(Model $model): bool
    {
        $except = Config::get('tenancy.organization_scope.except', [
            Organization::class,
        ]);

        if (in_array($model::class, $except, true)) {
            return false;
        }

        if (in_array('organization_id', $model->getFillable(), true)) {
            return true;
        }

        try {
            return $model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'organization_id');
        } catch (Throwable) {
            return false;
        }
    }

    private static function resolveSlugFromHost(string $host): ?string
    {
        $segments = explode('.', $host);

        if (count($segments) >= 3) {
            $slug = $segments[0];

            return in_array($slug, ['www', 'api'], true) ? null : $slug;
        }

        if (count($segments) === 2 && $segments[1] === 'localhost') {
            return $segments[0];
        }

        return null;
    }
}
