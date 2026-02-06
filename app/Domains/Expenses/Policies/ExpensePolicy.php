<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Policies;

use App\Domains\Expenses\Enums\ExpenseStatus;
use App\Domains\Expenses\Models\Expense;
use App\Domains\Identity\Models\User;

class ExpensePolicy
{
    public function view(User $user, Expense $expense): bool
    {
        if ($user->organization_id !== $expense->organization_id) {
            return false;
        }

        return $user->hasPermission('expenses.manage')
            || $user->hasPermission('expenses.view')
            || ($user->hasPermission('expenses.view_own') && $expense->user_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('expenses.manage') || $user->hasPermission('expenses.create');
    }

    public function update(User $user, Expense $expense): bool
    {
        return $user->organization_id === $expense->organization_id
            && $expense->canBeEdited()
            && ($user->hasPermission('expenses.manage')
                || ($user->hasPermission('expenses.create') && $expense->user_id === $user->id));
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $user->organization_id === $expense->organization_id
            && $expense->status === ExpenseStatus::Pending
            && ($user->hasPermission('expenses.manage') || $expense->user_id === $user->id);
    }

    public function approve(User $user, Expense $expense): bool
    {
        return $user->organization_id === $expense->organization_id
            && $user->hasPermission('expenses.manage')
            && $expense->status === ExpenseStatus::Pending;
    }

    public function submit(User $user, Expense $expense): bool
    {
        return $user->organization_id === $expense->organization_id
            && ($user->hasPermission('expenses.manage') || $expense->user_id === $user->id)
            && $expense->status === ExpenseStatus::Pending;
    }
}
