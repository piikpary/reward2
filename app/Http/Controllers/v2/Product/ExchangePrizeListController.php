<?php

namespace App\Http\Controllers\v2\Product;

use App\Http\Controllers\Controller;
use App\Models\ExchangePrize;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ExchangePrizeListController extends Controller
{
    public function index(
        Request $request
    ): JsonResponse {
        $page = max(
            1,
            (int) $request->input('page', 1)
        );

        $perPage = min(
            100,
            max(
                1,
                (int) $request->input(
                    'per_page',
                    20
                )
            )
        );

        $categoryId = $this->parseCategoryId(
            $request->input('category_id')
        );

        $version = (int) Cache::get(
            'reward2:exchange-prizes:version',
            1
        );

        $cacheKey = implode(':', [
            'reward2',
            'api',
            'exchange-prize-list',
            "v{$version}",
            "page-{$page}",
            "per-page-{$perPage}",
            'category-' . ($categoryId ?? 'all'),
        ]);

        $result = Cache::remember(
            $cacheKey,
            now()->addSeconds(60),
            function () use (
                $page,
                $perPage,
                $categoryId
            ): array {
                $products = ExchangePrize::query()
                    ->select([
                        'id',
                        'product_category_id',
                        'image_path',
                        'title',
                        'exchange_discount_amount',
                        'unit',
                        'created_at',
                    ])
                    ->with([
                        'category:id,name',
                    ])
                    ->when(
                        $categoryId,
                        fn ($query) =>
                            $query->where(
                                'product_category_id',
                                $categoryId
                            )
                    )
                    ->latest('id')
                    ->paginate(
                        $perPage,
                        ['*'],
                        'page',
                        $page
                    );

                $items = collect(
                    $products->items()
                )
                    ->map(
                        fn (
                            ExchangePrize $product
                        ): array => [
                            'id' =>
                                $this->formatProductId(
                                    $product->id
                                ),

                            'productItemImage' =>
                                $product->imageUrl(),

                            'productItemTitle' =>
                                $product->title,

                            'exchangeDiscountAmount' =>
                                (float) $product
                                    ->exchange_discount_amount,

                            'productItemCategory' =>
                                $product
                                    ->category
                                    ?->name
                                ?? 'Uncategorized',

                            'unit' =>
                                $product->unit,
                        ]
                    )
                    ->values()
                    ->all();

                return [
                    'data' => $items,

                    'pagination' => [
                        'currentPage' =>
                            $products->currentPage(),

                        'totalPages' =>
                            $products->lastPage(),

                        'totalItems' =>
                            $products->total(),

                        'itemsPerPage' =>
                            $products->perPage(),
                    ],
                ];
            }
        );

        return response()->json([
            'success' => true,

            'message' =>
                'Products retrieved successfully',

            'data' =>
                $result['data'],

            'pagination' =>
                $result['pagination'],
        ]);
    }

    private function parseCategoryId(
        mixed $value
    ): ?int {
        if (
            $value === null
            || $value === ''
        ) {
            return null;
        }

        if (
            is_string($value)
            && str_starts_with(
                strtolower($value),
                'cat_'
            )
        ) {
            $value = substr($value, 4);
        }

        $categoryId = (int) $value;

        return $categoryId > 0
            ? $categoryId
            : null;
    }

    private function formatProductId(
        int $productId
    ): string {
        return 'prod_' . str_pad(
            (string) $productId,
            3,
            '0',
            STR_PAD_LEFT
        );
    }
}