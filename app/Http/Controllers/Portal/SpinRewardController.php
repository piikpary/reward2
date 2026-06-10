<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\SpinReward;
use Illuminate\Http\Request;

class SpinRewardController extends Controller
{
    public function index()
    {
        $spinRewards = SpinReward::orderBy('sort_order')->paginate(10);

        return view('portal.spin-rewards.index', compact('spinRewards'));
    }

    public function create()
    {
        return view('portal.spin-rewards.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'discount_percentage' => ['required', 'integer', 'min:1', 'max:100'],
            'chance_weight' => ['required', 'integer', 'min:0', 'max:100000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'boolean'],
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        SpinReward::create($validated);

        return redirect()
            ->route('portal.spin-rewards.index')
            ->with('success', 'Spin reward created successfully.');
    }

    public function edit(SpinReward $spinReward)
    {
        return view('portal.spin-rewards.edit', compact('spinReward'));
    }

    public function update(Request $request, SpinReward $spinReward)
    {
        $validated = $request->validate([
            'discount_percentage' => ['required', 'integer', 'min:1', 'max:100'],
            'chance_weight' => ['required', 'integer', 'min:0', 'max:100000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'boolean'],
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $spinReward->update($validated);

        return redirect()
            ->route('portal.spin-rewards.index')
            ->with('success', 'Spin reward updated successfully.');
    }

    public function destroy(SpinReward $spinReward)
    {
        $spinReward->delete();

        return redirect()
            ->route('portal.spin-rewards.index')
            ->with('success', 'Spin reward deleted successfully.');
    }
}