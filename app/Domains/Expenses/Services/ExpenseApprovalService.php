<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Services;

use App\Domains\Expenses\Enums\ExpenseStatus;
use App\Domains\Expenses\Exceptions\InvalidExpenseStatusException;
use App\Domains\Expenses\Models\Expense;
use App\Domains\Identity\Models\User;

class ExpenseApprovalService
{
    public function submitForApproval(Expense $expense): void
    {
        if ($expense->status !== ExpenseStatus::Pending) {
            throw new InvalidExpenseStatusException($expense->status, ExpenseStatus::Pending);
        }

        $expense->save();
    }

    public function approve(Expense $expense, User $approver): void
    {
        if (! $this->canApprove($approver, $expense)) {
            throw new InvalidExpenseStatusException($expense->status, ExpenseStatus::Approved);
        }

        $expense->markAsApproved($approver);
    }

    public function reject(Expense $expense, string $reason): void
    {
        if (! $expense->status->canTransitionTo(ExpenseStatus::Rejected)) {
            throw new InvalidExpenseStatusException($expense->status, ExpenseStatus::Rejected);
        }

        $existingNotes = $expense->notes !== null ? trim((string) $expense->notes) : '';
        $expense->notes = trim($existingNotes."\nRejected reason: {$reason}");
        $expense->markAsRejected();
    }

    public function canApprove(User $user, Expense $expense): bool
    {
        return $user->organization_id === $expense->organization_id
            && $user->hasPermission('expenses.manage')
            && $expense->status === ExpenseStatus::Pending;
    }
}
