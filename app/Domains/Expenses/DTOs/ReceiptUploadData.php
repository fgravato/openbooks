<?php

declare(strict_types=1);

namespace App\Domains\Expenses\DTOs;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

readonly class ReceiptUploadData
{
    public function __construct(
        public int $expenseId,
        public UploadedFile $file,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            expenseId: (int) $request->integer('expense_id'),
            file: $request->file('file'),
        );
    }
}
