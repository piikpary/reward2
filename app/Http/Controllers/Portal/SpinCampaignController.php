<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\SpinCampaign;
use App\Models\SpinSpecialCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpinCampaignController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $campaigns = SpinCampaign::query()
            ->withCount('specialCases')
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($status !== null && $status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('portal.spin-campaigns.index', compact('campaigns', 'search', 'status'));
    }

    public function create()
    {
        return view('portal.spin-campaigns.create', [
            'campaign' => new SpinCampaign(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'priority' => ['required', 'integer', 'min:0'],
            'total_cases' => ['required', 'integer', 'min:1'],
            'spins_per_case' => ['required', 'integer', 'min:1'],
            'normal_discount_total' => ['required', 'integer', 'min:1'],
            'status' => ['required'],
        ]);

        $validated['rule_type'] = 'standard';
        $validated['total_spins_used'] = 0;

        SpinCampaign::create($validated);

        return redirect()
            ->route('portal.spin-campaigns.index')
            ->with('success', 'Spin campaign created successfully.');
    }

    public function show(SpinCampaign $spinCampaign)
    {
        $spinCampaign->load(['specialCases' => function ($query) {
            $query->orderBy('case_number');
        }]);

        return view('portal.spin-campaigns.show', compact('spinCampaign'));
    }

    public function edit(SpinCampaign $spinCampaign)
    {
        return view('portal.spin-campaigns.edit', [
            'campaign' => $spinCampaign,
        ]);
    }

    public function update(Request $request, SpinCampaign $spinCampaign)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'priority' => ['required', 'integer', 'min:0'],
            'total_cases' => ['required', 'integer', 'min:1'],
            'spins_per_case' => ['required', 'integer', 'min:1'],
            'normal_discount_total' => ['required', 'integer', 'min:1'],
            'status' => ['required'],
        ]);

        $validated['rule_type'] = $spinCampaign->rule_type ?: 'standard';

        $spinCampaign->update($validated);

        return redirect()
            ->route('portal.spin-campaigns.show', $spinCampaign)
            ->with('success', 'Spin campaign updated successfully.');
    }

    public function destroy(SpinCampaign $spinCampaign)
    {
        $spinCampaign->delete();

        return redirect()
            ->route('portal.spin-campaigns.index')
            ->with('success', 'Spin campaign deleted successfully.');
    }

    public function storeSpecialCase(Request $request, SpinCampaign $spinCampaign)
    {
        $validated = $request->validate([
            'case_number' => ['required', 'integer', 'min:1', 'max:' . $spinCampaign->total_cases],
            'total_discount' => ['required', 'integer', 'min:1'],
            'status' => ['required'],
        ]);

        SpinSpecialCase::updateOrCreate(
            [
                'spin_campaign_id' => $spinCampaign->id,
                'case_number' => $validated['case_number'],
            ],
            [
                'total_discount' => $validated['total_discount'],
                'status' => $validated['status'],
            ]
        );

        return back()->with('success', 'Special case saved successfully.');
    }

    public function deleteSpecialCase(SpinCampaign $spinCampaign, SpinSpecialCase $specialCase)
    {
        if ((int) $specialCase->spin_campaign_id !== (int) $spinCampaign->id) {
            abort(404);
        }

        $specialCase->delete();

        return back()->with('success', 'Special case deleted successfully.');
    }

    public function resetProgress(SpinCampaign $spinCampaign)
    {
        DB::transaction(function () use ($spinCampaign) {
            DB::table('spin_case_sequences')
                ->where('spin_campaign_id', $spinCampaign->id)
                ->delete();

            DB::table('spin_results')
                ->where('spin_campaign_id', $spinCampaign->id)
                ->delete();

            $spinCampaign->update([
                'total_spins_used' => 0,
            ]);
        });

        return back()->with('success', 'Campaign progress reset successfully.');
    }
}