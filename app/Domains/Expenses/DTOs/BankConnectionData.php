<?php

declare(strict_types=1);

namespace App\Domains\Expenses\DTOs;

use App\Domains\Expenses\Enums\BankAccountType;
use Illuminate\Http\Request;

readonly class BankConnectionData
{
    public function __construct(
        public string $name,
        public string $institutionName,
        public string $accessToken,
        public string $itemId,
        public string $accountMask,
        public BankAccountType $accountType,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: (string) $request->input('name'),
            institutionName: (string) $request->input('institution_name'),
            accessToken: (string) $request->input('access_token'),
            itemId: (string) $request->input('item_id'),
            accountMask: (string) $request->input('account_mask'),
            accountType: BankAccountType::from((string) $request->input('account_type')),
        );
    }
}
