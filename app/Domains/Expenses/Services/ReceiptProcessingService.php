<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Services;

use App\Domains\Expenses\Exceptions\ReceiptUploadException;
use App\Domains\Expenses\Models\Expense;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ReceiptProcessingService
{
    public function uploadReceipt(Expense $expense, UploadedFile $file): string
    {
        $path = $file->store('receipts/expenses', 'public');

        if ($path === false || $path === '') {
            throw new ReceiptUploadException('Failed to upload receipt.');
        }

        $expense->receipt_path = $path;
        $expense->save();

        $this->generateThumbnail($path);

        return $path;
    }

    public function generateThumbnail(string $path): string
    {
        $thumbnailPath = preg_replace('/(\.[^.]+)$/', '-thumb$1', $path);
        $thumbnailPath = $thumbnailPath === null ? $path.'-thumb' : $thumbnailPath;

        if (Storage::disk('public')->exists($path)) {
            $contents = Storage::disk('public')->get($path);
            Storage::disk('public')->put($thumbnailPath, $contents);
        }

        return $thumbnailPath;
    }

    public function deleteReceipt(Expense $expense): void
    {
        if ($expense->receipt_path === null) {
            return;
        }

        Storage::disk('public')->delete($expense->receipt_path);
        $thumbnailPath = preg_replace('/(\.[^.]+)$/', '-thumb$1', $expense->receipt_path);

        if ($thumbnailPath !== null) {
            Storage::disk('public')->delete($thumbnailPath);
        }

        $expense->receipt_path = null;
        $expense->save();
    }

    public function getReceiptUrl(Expense $expense): string
    {
        if ($expense->receipt_path === null) {
            return '';
        }

        return Storage::disk('public')->url((string) $expense->receipt_path);
    }
}
