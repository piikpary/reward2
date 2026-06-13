<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\SpinCampaign;
use App\Models\SpinSubCampaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpinSubCampaignController extends Controller
{
    public function index(SpinCampaign $campaign)
    {
        $subCampaigns = $campaign->subCampaigns()
            ->orderByDesc('priority')
            ->latest()
            ->paginate(10);

        return view(
            'portal.spin-sub-campaigns.index',
            compact('campaign', 'subCampaigns')
        );
    }

    public function create(SpinCampaign $campaign)
    {
        return view(
            'portal.spin-sub-campaigns.create',
            compact('campaign')
        );
    }

    public function store(
        Request $request,
        SpinCampaign $campaign
    ) {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'total_cases' => [
                'required',
                'integer',
                'min:1',
            ],
            'spins_per_case' => [
                'required',
                'integer',
                'min:1',
            ],
            'normal_discount_total' => [
                'required',
                'numeric',
                'min:1',
            ],
            'priority' => [
                'required',
                'integer',
                'min:1',
            ],
            'status' => [
                'required',
                'in:active,inactive',
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ]);

        $validated['spin_campaign_id'] = $campaign->id;
        $validated['total_spins_used'] = 0;

        SpinSubCampaign::create($validated);

        return redirect()
            ->route(
                'portal.spin-campaigns.sub-campaigns.index',
                $campaign
            )
            ->with(
                'success',
                'Subcampaign created successfully.'
            );
    }

    public function edit(
        SpinCampaign $campaign,
        SpinSubCampaign $subCampaign
    ) {
        $this->ensureBelongsToCampaign(
            $campaign,
            $subCampaign
        );

        return view(
            'portal.spin-sub-campaigns.edit',
            compact('campaign', 'subCampaign')
        );
    }

    public function update(
        Request $request,
        SpinCampaign $campaign,
        SpinSubCampaign $subCampaign
    ) {
        $this->ensureBelongsToCampaign(
            $campaign,
            $subCampaign
        );

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'total_cases' => [
                'required',
                'integer',
                'min:1',
            ],
            'spins_per_case' => [
                'required',
                'integer',
                'min:1',
            ],
            'normal_discount_total' => [
                'required',
                'numeric',
                'min:1',
            ],
            'priority' => [
                'required',
                'integer',
                'min:1',
            ],
            'status' => [
                'required',
                'in:active,inactive',
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ]);

        $totalAllowedSpins =
            (int) $validated['total_cases']
            * (int) $validated['spins_per_case'];

        if (
            (int) $subCampaign->total_spins_used
            > $totalAllowedSpins
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'total_cases' =>
                        'The new spin quota cannot be lower than the spins already used.',
                ]);
        }

        /*
         * Do not reduce total cases below an existing
         * special case number.
         */
        $highestSpecialCaseNumber = $subCampaign
            ->specialCases()
            ->max('case_number');

        if (
            $highestSpecialCaseNumber !== null
            && (int) $validated['total_cases']
                < (int) $highestSpecialCaseNumber
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'total_cases' =>
                        "Total cases cannot be lower than special case number {$highestSpecialCaseNumber}.",
                ]);
        }

        $subCampaign->update($validated);

        return redirect()
            ->route(
                'portal.spin-campaigns.sub-campaigns.index',
                $campaign
            )
            ->with(
                'success',
                'Subcampaign updated successfully.'
            );
    }

    public function destroy(
        SpinCampaign $campaign,
        SpinSubCampaign $subCampaign
    ) {
        $this->ensureBelongsToCampaign(
            $campaign,
            $subCampaign
        );

        if ((int) $subCampaign->total_spins_used > 0) {
            return back()->withErrors([
                'delete' =>
                    'This subcampaign already has used spins and cannot be deleted.',
            ]);
        }

        $hasSpinResults = DB::table('spin_results')
            ->where(
                'spin_sub_campaign_id',
                $subCampaign->id
            )
            ->exists();

        if ($hasSpinResults) {
            return back()->withErrors([
                'delete' =>
                    'This subcampaign already has spin results and cannot be deleted.',
            ]);
        }

        $hasCaseSequences = DB::table('spin_case_sequences')
            ->where(
                'spin_sub_campaign_id',
                $subCampaign->id
            )
            ->exists();

        if ($hasCaseSequences) {
            return back()->withErrors([
                'delete' =>
                    'This subcampaign already has case sequence history and cannot be deleted.',
            ]);
        }

        DB::transaction(function () use ($subCampaign) {
            /*
             * Special cases have no spin history yet,
             * so they can be removed with the subcampaign.
             */
            DB::table('spin_special_cases')
                ->where(
                    'spin_sub_campaign_id',
                    $subCampaign->id
                )
                ->delete();

            $subCampaign->delete();
        });

        return redirect()
            ->route(
                'portal.spin-campaigns.sub-campaigns.index',
                $campaign
            )
            ->with(
                'success',
                'Subcampaign deleted successfully.'
            );
    }

    private function ensureBelongsToCampaign(
        SpinCampaign $campaign,
        SpinSubCampaign $subCampaign
    ): void {
        abort_unless(
            (int) $subCampaign->spin_campaign_id
                === (int) $campaign->id,
            404
        );
    }
}