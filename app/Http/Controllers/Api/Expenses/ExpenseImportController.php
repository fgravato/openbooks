<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Expenses;

use App\Domains\Expenses\Services\ExpenseImportService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Expenses\ImportExpensesRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Expense Import
 */
class ExpenseImportController extends Controller
{
    public function __construct(
        protected ExpenseImportService $importService
    ) {}

    /**
     * Preview CSV import
     */
    public function previewCsv(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);
        $rows = [];
        for ($i = 0; $i < 5 && ($row = fgetcsv($handle)) !== false; $i++) {
            $rows[] = $row;
        }
        fclose($handle);

        return response()->json([
            'header' => $header,
            'preview' => $rows,
        ]);
    }

    /**
     * Process CSV import
     */
    public function importCsv(ImportExpensesRequest $request): JsonResponse
    {
        $file = $request->file('file');
        
        $result = $this->importService->importFromCsv(
            $file->getRealPath(),
            (int) $request->category_id,
            $request->mappings
        );

        return response()->json($result);
    }
}
