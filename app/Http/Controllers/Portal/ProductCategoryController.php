<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class ProductCategoryController extends Controller
{
    public function index(): View
    {
        $categories = ProductCategory::query()
            ->withCount('exchangePrizes')
            ->latest('id')
            ->paginate(20);

        return view(
            'portal.product-categories.index',
            compact('categories')
        );
    }

    public function create(): View
    {
        return view(
            'portal.product-categories.create'
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                'unique:product_categories,name',
            ],

            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        ProductCategory::query()->create([
            'name' => trim(
                $validated['name']
            ),

            'description' =>
                $validated['description']
                ?? null,
        ]);

        $this->clearApiCache();

        return redirect()
            ->route(
                'portal.product-categories.index'
            )
            ->with(
                'success',
                'Product category created successfully.'
            );
    }

    public function edit(
        ProductCategory $productCategory
    ): View {
        return view(
            'portal.product-categories.edit',
            compact('productCategory')
        );
    }

    public function update(
        Request $request,
        ProductCategory $productCategory
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',

                Rule::unique(
                    'product_categories',
                    'name'
                )->ignore(
                    $productCategory->id
                ),
            ],

            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $productCategory->update([
            'name' => trim(
                $validated['name']
            ),

            'description' =>
                $validated['description']
                ?? null,
        ]);

        $this->clearApiCache();

        return redirect()
            ->route(
                'portal.product-categories.index'
            )
            ->with(
                'success',
                'Product category updated successfully.'
            );
    }

    public function destroy(
        ProductCategory $productCategory
    ): RedirectResponse {
        if (
            $productCategory
                ->exchangePrizes()
                ->exists()
        ) {
            return back()->with(
                'error',
                'This category cannot be deleted because it is currently assigned to products.'
            );
        }

        $productCategory->delete();

        $this->clearApiCache();

        return redirect()
            ->route(
                'portal.product-categories.index'
            )
            ->with(
                'success',
                'Product category deleted successfully.'
            );
    }

    private function clearApiCache(): void
    {
        Cache::forget(
            'reward2:api:product-categories:v1'
        );

        Cache::increment(
            'reward2:exchange-prizes:version'
        );
    }
}