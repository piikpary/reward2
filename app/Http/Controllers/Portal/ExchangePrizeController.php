<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ExchangePrize;
use App\Models\ProductCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ExchangePrizeController extends Controller
{
    /**
     * Display exchange prize products.
     */
    public function index(): View
    {
        $exchangePrizes = ExchangePrize::query()
            ->with([
                'category:id,name',
            ])
            ->latest('id')
            ->paginate(20);

        return view(
            'portal.exchange-prizes.index',
            compact('exchangePrizes')
        );
    }

    /**
     * Show product creation form.
     */
    public function create(): View
    {
        $categories = ProductCategory::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        return view(
            'portal.exchange-prizes.create',
            compact('categories')
        );
    }

    /**
     * Store a new exchange prize product.
     */
    public function store(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'product_category_id' => [
                'required',
                'integer',
                'exists:product_categories,id',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'exchange_discount_amount' => [
                'required',
                'numeric',
                'min:0',
                'max:999999999.99',
            ],

            'image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'unit' => [
                'required',
                'string',
                'max:100',
            ],
        ]);

        $imagePath = $request
            ->file('image')
            ->store(
                'exchange-prizes',
                'public'
            );

        ExchangePrize::query()->create([
            'product_category_id' =>
                $validated['product_category_id'],

            'title' =>
                trim($validated['title']),

            'exchange_discount_amount' =>
                $validated['exchange_discount_amount'],

            'unit' =>
                trim($validated['unit']),

            'image_path' =>
                $imagePath,
        ]);

        $this->increaseApiCacheVersion();

        return redirect()
            ->route(
                'portal.exchange-prizes.index'
            )
            ->with(
                'success',
                'Exchange prize product created successfully.'
            );
    }

    /**
     * Show product edit form.
     */
    public function edit(
        ExchangePrize $exchangePrize
    ): View {
        $categories = ProductCategory::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        return view(
            'portal.exchange-prizes.edit',
            compact(
                'exchangePrize',
                'categories'
            )
        );
    }

    /**
     * Update an exchange prize product.
     */
    public function update(
        Request $request,
        ExchangePrize $exchangePrize
    ): RedirectResponse {
        $validated = $request->validate([
            'product_category_id' => [
                'required',
                'integer',
                'exists:product_categories,id',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'exchange_discount_amount' => [
                'required',
                'numeric',
                'min:0',
                'max:999999999.99',
            ],
            'unit' => [
                'required',
                'string',
                'max:100',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        $updateData = [
            'product_category_id' =>
                $validated['product_category_id'],

            'title' =>
                trim($validated['title']),

            'exchange_discount_amount' =>
                $validated['exchange_discount_amount'],

            'unit' =>
                trim($validated['unit']),
        ];

        if ($request->hasFile('image')) {
            $oldImagePath =
                $exchangePrize->image_path;

            $newImagePath = $request
                ->file('image')
                ->store(
                    'exchange-prizes',
                    'public'
                );

            $updateData['image_path'] =
                $newImagePath;

            /*
             * Delete the old image only after the new
             * image has been stored successfully.
             */
            if (
                !empty($oldImagePath)
                && !str_starts_with(
                    $oldImagePath,
                    'http://'
                )
                && !str_starts_with(
                    $oldImagePath,
                    'https://'
                )
            ) {
                Storage::disk('public')->delete(
                    $oldImagePath
                );
            }
        }

        $exchangePrize->update(
            $updateData
        );

        $this->increaseApiCacheVersion();

        return redirect()
            ->route(
                'portal.exchange-prizes.index'
            )
            ->with(
                'success',
                'Exchange prize product updated successfully.'
            );
    }

    /**
     * Delete an exchange prize product.
     */
    public function destroy(
        ExchangePrize $exchangePrize
    ): RedirectResponse {
        $imagePath =
            $exchangePrize->image_path;

        $exchangePrize->delete();

        if (
            !empty($imagePath)
            && !str_starts_with(
                $imagePath,
                'http://'
            )
            && !str_starts_with(
                $imagePath,
                'https://'
            )
        ) {
            Storage::disk('public')->delete(
                $imagePath
            );
        }

        $this->increaseApiCacheVersion();

        return redirect()
            ->route(
                'portal.exchange-prizes.index'
            )
            ->with(
                'success',
                'Exchange prize product deleted successfully.'
            );
    }

    /**
     * Change the version used by the cached product API.
     *
     * Old cached pages become unused immediately.
     */
    private function increaseApiCacheVersion(): void
    {
        $cacheKey =
            'reward2:exchange-prizes:version';

        $currentVersion = (int) Cache::get(
            $cacheKey,
            1
        );

        Cache::forever(
            $cacheKey,
            $currentVersion + 1
        );
    }
}