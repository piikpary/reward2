<?php

namespace App\Http\Controllers\v2\Product;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ProductCategoryController
    extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Cache::remember(
            'reward2:api:product-categories:v1',
            now()->addMinutes(10),
            function (): array {
                return ProductCategory::query()
                    ->orderBy('name')
                    ->get([
                        'id',
                        'name',
                        'description',
                    ])
                    ->map(
                        fn (
                            ProductCategory $category
                        ): array => [
                            'id' =>
                                $this->formatCategoryId(
                                    $category->id
                                ),

                            'categoryName' =>
                                $category->name,

                            'categoryDescription' =>
                                $category->description,
                        ]
                    )
                    ->values()
                    ->all();
            }
        );

        return response()->json([
            'success' => true,

            'message' =>
                'Categories retrieved successfully',

            'data' => $categories,
        ]);
    }

    private function formatCategoryId(
        int $categoryId
    ): string {
        return 'cat_' . str_pad(
            (string) $categoryId,
            3,
            '0',
            STR_PAD_LEFT
        );
    }
}