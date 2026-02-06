<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Policies;

use App\Domains\Expenses\Models\ExpenseCategory;
use App\Domains\Identity\Models\User;

class ExpenseCategoryPolicy
{
    public function view(User $user, ExpenseCategory $category): bool
    {
        return $user->organization_id === $category->organization_id
            && ($user->hasPermission('expenses.manage') || $user->hasPermission('expenses.view'));
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('expenses.manage');
    }

    public function update(User $user, ExpenseCategory $category): bool
    {
        return $user->organization_id === $category->organization_id
            && $user->hasPermission('expenses.manage');
    }

    public function delete(User $user, ExpenseCategory $category): bool
    {
        return $user->organization_id === $category->organization_id
            && $user->hasPermission('expenses.manage')
            && ! $category->is_default;
    }
}
