<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\SpinCampaign;
use App\Models\SpinSubCampaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\SpinSpecialRewardService;

class SpinSubCampaignController extends Controller
{
    public function index(SpinCampaign $campaign)
{
    $subCampaigns = $campaign->subCampaigns()
    ->with([
        'specialReward.winner',
    ])
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
        [
            'campaign' => $campaign,
            'subCampaign' => new SpinSubCampaign(),
        ]
    );
}
    public function store(
    Request $request,
    SpinCampaign $campaign,
    SpinSpecialRewardService $specialRewardService
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

        /*
         * Hidden special spin discount.
         */
        'special_discount_enabled' => [
            'nullable',
            'boolean',
        ],

        'special_discount' => [
            'nullable',
            'required_if:special_discount_enabled,1',
            'numeric',
            'min:0.01',
        ],
    ]);

    try {
        DB::transaction(function () use (
            $request,
            $campaign,
            $validated,
            $specialRewardService
        ) {
            /*
             * Do not save special reward fields in the
             * spin_sub_campaigns table.
             */
            $subCampaignData = collect($validated)
                ->except([
                    'special_discount_enabled',
                    'special_discount',
                ])
                ->toArray();

            $subCampaignData['spin_campaign_id'] =
                $campaign->id;

            $subCampaignData['total_spins_used'] = 0;

            $subCampaign = SpinSubCampaign::create(
                $subCampaignData
            );

            /*
             * The service assigns the special discount to one
             * random hidden position inside the existing quota.
             */
            $specialRewardService
                ->saveSubCampaignReward(
                    $campaign,
                    $subCampaign,
                    $request->boolean(
                        'special_discount_enabled'
                    ),
                    isset($validated['special_discount'])
                        ? (float) $validated['special_discount']
                        : null
                );
        });
    } catch (\RuntimeException $exception) {
        return back()
            ->withInput()
            ->withErrors([
                'special_discount' =>
                    $exception->getMessage(),
            ]);
    }

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

    $subCampaign->load('specialReward');

    return view(
        'portal.spin-sub-campaigns.edit',
        compact('campaign', 'subCampaign')
    );
}
    public function update(
    Request $request,
    SpinCampaign $campaign,
    SpinSubCampaign $subCampaign,
    SpinSpecialRewardService $specialRewardService
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

        /*
         * Hidden special spin discount.
         */
        'special_discount_enabled' => [
            'nullable',
            'boolean',
        ],

        'special_discount' => [
            'nullable',
            'required_if:special_discount_enabled,1',
            'numeric',
            'min:0.01',
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
     * Keep the existing special-case validation unchanged.
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

    try {
        DB::transaction(function () use (
            $request,
            $campaign,
            $subCampaign,
            $validated,
            $specialRewardService
        ) {
            /*
             * Keep special reward fields out of the
             * spin_sub_campaigns update.
             */
            $subCampaignData = collect($validated)
                ->except([
                    'special_discount_enabled',
                    'special_discount',
                ])
                ->toArray();

            $subCampaign->update(
                $subCampaignData
            );

            /*
             * Keep the existing hidden position when valid.
             * If quota changes make it invalid, the service
             * assigns another unused hidden position.
             */
            $specialRewardService
                ->saveSubCampaignReward(
                    $campaign,
                    $subCampaign->fresh(),
                    $request->boolean(
                        'special_discount_enabled'
                    ),
                    isset($validated['special_discount'])
                        ? (float) $validated['special_discount']
                        : null
                );
        });
    } catch (\RuntimeException $exception) {
        return back()
            ->withInput()
            ->withErrors([
                'special_discount' =>
                    $exception->getMessage(),
            ]);
    }

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