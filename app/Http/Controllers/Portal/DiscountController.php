<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DiscountController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $discounts = Discount::query()
            ->when($search, function ($query) use ($search) {
                $query->where('discount_percentage', 'like', "%{$search}%");
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderBy('discount_percentage')
            ->paginate(10)
            ->withQueryString();

        return view('portal.discounts.index', compact('discounts', 'search', 'status'));
    }

    public function create()
    {
        return view('portal.discounts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'discount_percentage' => [
                'required',
                'integer',
                'min:1',
                'max:100',
                'unique:discounts,discount_percentage',
            ],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        Discount::create($validated);

        return redirect()
            ->route('portal.discounts.index')
            ->with('success', 'Discount created successfully.');
    }

    public function edit(Discount $discount)
    {
        return view('portal.discounts.edit', compact('discount'));
    }

    public function update(Request $request, Discount $discount)
    {
        $validated = $request->validate([
            'discount_percentage' => [
                'required',
                'integer',
                'min:1',
                'max:100',
                Rule::unique('discounts', 'discount_percentage')->ignore($discount->id),
            ],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $discount->update($validated);

        return redirect()
            ->route('portal.discounts.index')
            ->with('success', 'Discount updated successfully.');
    }

    public function destroy(Discount $discount)
    {
        $discount->delete();

        return redirect()
            ->route('portal.discounts.index')
            ->with('success', 'Discount deleted successfully.');
    }

    public function toggleStatus(Discount $discount)
    {
        $discount->update([
            'status' => $discount->status === 'active' ? 'inactive' : 'active',
        ]);

        return redirect()
            ->route('portal.discounts.index')
            ->with('success', 'Discount status updated successfully.');
    }
}