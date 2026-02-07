<?php

declare(strict_types=1);

namespace App\Domains\Expenses\DTOs;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;

readonly class ReceiptUploadData
{
    public function __construct(
        public int $expenseId,
        public UploadedFile $file,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $file = $request->file('file') ?? $request->file('receipt');

        if (! $file instanceof UploadedFile) {
            throw new InvalidArgumentException('A valid uploaded receipt file is required.');
        }

        return new self(
            expenseId: (int) $request->integer('expense_id'),
            file: $file,
        );
    }
}
