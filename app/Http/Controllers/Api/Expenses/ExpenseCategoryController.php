<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Expenses;

use App\Domains\Expenses\Models\ExpenseCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Expenses\StoreExpenseCategoryRequest;
use App\Http\Requests\Expenses\UpdateExpenseCategoryRequest;
use App\Http\Resources\Expenses\ExpenseCategoryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Expense Categories
 */
class ExpenseCategoryController extends Controller
{
    /**
     * List expense categories
     */
    public function index(): AnonymousResourceCollection
    {
        $categories = ExpenseCategory::query()
            ->withCount('expenses')
            ->orderBy('name')
            ->get();

        return ExpenseCategoryResource::collection($categories);
    }

    /**
     * Create category
     */
    public function store(StoreExpenseCategoryRequest $request): ExpenseCategoryResource
    {
        $category = new ExpenseCategory($request->validated());
        $category->organization_id = $request->user()->organization_id;
        $category->save();

        return new ExpenseCategoryResource($category);
    }

    /**
     * Show category
     */
    public function show(ExpenseCategory $category): ExpenseCategoryResource
    {
        return new ExpenseCategoryResource($category->loadCount('expenses'));
    }

    /**
     * Update category
     */
    public function update(UpdateExpenseCategoryRequest $request, ExpenseCategory $category): ExpenseCategoryResource
    {
        $category->update($request->validated());

        return new ExpenseCategoryResource($category);
    }

    /**
     * Delete category
     */
    public function destroy(ExpenseCategory $category): JsonResponse
    {
        if ($category->expenses()->exists()) {
            return response()->json(['message' => 'Cannot delete category with expenses.'], 422);
        }

        $category->delete();

        return response()->json(null, 204);
    }
}
