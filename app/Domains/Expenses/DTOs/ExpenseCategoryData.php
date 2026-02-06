<?php

declare(strict_types=1);

namespace App\Domains\Expenses\DTOs;

use Illuminate\Http\Request;

readonly class ExpenseCategoryData
{
    public function __construct(
        public string $name,
        public ?string $description,
        public ?int $parentId,
        public string $color,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: (string) $request->input('name'),
            description: $request->filled('description') ? (string) $request->input('description') : null,
            parentId: $request->filled('parent_id') ? (int) $request->integer('parent_id') : null,
            color: (string) $request->input('color', '#64748b'),
        );
    }
}
