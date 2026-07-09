<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\SpinCampaign;
use App\Models\SpinSpecialCase;
use App\Models\SpinSubCampaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\SpinSpecialRewardService;

class SpinCampaignController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $campaigns = SpinCampaign::query()
            ->with([
                'subCampaigns' => function ($query) {
                    $query
                        ->withCount('specialCases')
                        ->orderByDesc('priority');
                },
            ])
            ->withCount('subCampaigns')
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when(
                $status !== null && $status !== '',
                function ($query) use ($status) {
                    $query->where('status', (int) $status);
                }
            )
            ->orderByDesc('priority')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $totalCampaigns = SpinCampaign::count();

        $activeCampaigns = SpinCampaign::query()
            ->where('status', 1)
            ->count();

        return view(
            'portal.spin-campaigns.index',
            compact(
                'campaigns',
                'search',
                'status',
                'totalCampaigns',
                'activeCampaigns'
            )
        );
    }

    public function create()
{
    return view(
        'portal.spin-campaigns.create',
        [
            'campaign' => new SpinCampaign(),
        ]
    );
}
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'max_spin_qty' => [
                    'required',
                    'integer',
                    'min:1',
                ],
            'start_date' => [
                'required',
                'date',
            ],
            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
            ],
            'priority' => [
                'required',
                'integer',
                'min:0',
            ],
            'status' => [
                'required',
                'in:0,1',
            ],
        ]);

        $validated['status'] = (int) $validated['status'];

        /*
        |--------------------------------------------------------------------------
        | Prevent overlapping active campaigns
        |--------------------------------------------------------------------------
        |
        | Only one active main campaign should run during the same period.
        | Multiple subcampaigns are allowed inside that main campaign.
        |
        */

        if (
            $validated['status'] === 1
            && $this->hasActiveCampaignOverlap(
                $validated['start_date'],
                $validated['end_date']
            )
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'start_date' =>
                        'Another active campaign already overlaps this period. Please deactivate the old campaign or change the dates.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Temporary old-column defaults
        |--------------------------------------------------------------------------
        |
        | These old main-campaign fields are no longer used for the actual
        | spin rule. The actual rules are stored in spin_sub_campaigns.
        |
        | Keep these only while the old database columns are still required.
        |
        */

        $validated['total_cases'] = 0;
        $validated['spins_per_case'] = 0;
        $validated['normal_discount_total'] = 0;
        $validated['total_spins_used'] = 0;

        /*
         * Keep "standard" if the current database enum only supports it.
         */
        $validated['rule_type'] = 'standard';

        $campaign = SpinCampaign::create($validated);

        return redirect()
            ->route('portal.spin-campaigns.index')
            ->with(
                'success',
                'Main campaign created successfully.'
            );
    }

    public function show(SpinCampaign $spinCampaign)
    {
        $spinCampaign->load([
            'subCampaigns' => function ($query) {
                $query
                    ->withCount('specialCases')
                    ->orderByDesc('priority')
                    ->latest();
            },
        ]);

        return view('portal.spin-campaigns.show', [
            'campaign' => $spinCampaign,
        ]);
    }

    public function edit(SpinCampaign $spinCampaign)
    {
        $spinCampaign->load([
            'subCampaigns' => function ($query) {
                $query->orderByDesc('priority');
            },
            'mainSpecialRewards',
        ]);

        return view('portal.spin-campaigns.edit', [
            'campaign' => $spinCampaign,
        ]);
    }

   public function update(
    Request $request,
    SpinCampaign $spinCampaign,
    SpinSpecialRewardService $specialRewardService
) {
    $validated = $request->validate([
        'name' => [
            'required',
            'string',
            'max:255',
        ],
        'description' => [
            'nullable',
            'string',
        ],
        'max_spin_qty' => [
            'required',
            'integer',
            'min:1',
        ],
        'start_date' => [
            'required',
            'date',
        ],
        'end_date' => [
            'required',
            'date',
            'after_or_equal:start_date',
        ],
        'priority' => [
            'required',
            'integer',
            'min:0',
        ],
        'status' => [
            'required',
            'in:0,1',
        ],

        /*
        |--------------------------------------------------------------------------
        | Hidden special-spin configuration
        |--------------------------------------------------------------------------
        */

        'special_discount_enabled' => [
            'nullable',
            'boolean',
        ],

        'special_discounts' => [
            'nullable',
            'array',
        ],

        'special_discounts.*' => [
            'required_if:special_discount_enabled,1',
            'numeric',
            'min:0.01',
        ],
    ]);

    $validated['status'] =
        (int) $validated['status'];

    /*
    |--------------------------------------------------------------------------
    | Prevent overlapping active campaigns
    |--------------------------------------------------------------------------
    */

    if (
        $validated['status'] === 1
        && $this->hasActiveCampaignOverlap(
            $validated['start_date'],
            $validated['end_date'],
            $spinCampaign->id
        )
    ) {
        return back()
            ->withInput()
            ->withErrors([
                'start_date' =>
                    'Another active campaign already overlaps this period. Please deactivate the old campaign or change the dates.',
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Do not save special-spin fields in spin_campaigns
    |--------------------------------------------------------------------------
    |
    | They belong in spin_special_rewards.
    |
    */

    $campaignData = collect($validated)
    ->except([
        'special_discount_enabled',
        'special_discounts',
    ])
    ->toArray();

    try {
        DB::transaction(function () use (
            $request,
            $spinCampaign,
            $campaignData,
            $validated,
            $specialRewardService
        ) {
            /*
             * Keep existing main campaign update behavior.
             */
            $spinCampaign->update(
                $campaignData
            );

            /*
             * Configure only the hidden random special spin.
             *
             * The service:
             * - does not increase total spins;
             * - chooses one unused hidden spin position;
             * - keeps an existing valid position;
             * - stores the reward under main-campaign scope.
             */
            $specialRewardService
            ->saveMainCampaignReward(
                $spinCampaign->fresh(),
                $request->boolean(
                    'special_discount_enabled'
                ),
                $validated['special_discounts'] ?? []
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
    ->route('portal.spin-campaigns.index')
    ->with(
        'success',
        'Main campaign updated successfully.'
    );
}

    public function destroy(SpinCampaign $spinCampaign)
    {
        /*
        |--------------------------------------------------------------------------
        | Prevent deletion when used spins exist
        |--------------------------------------------------------------------------
        */

        $hasUsedSpins = $spinCampaign->subCampaigns()
            ->where('total_spins_used', '>', 0)
            ->exists();

        if ($hasUsedSpins) {
            return back()->withErrors([
                'delete' =>
                    'This campaign already has spin history and cannot be deleted.',
            ]);
        }

        $subCampaignIds = $spinCampaign
            ->subCampaigns()
            ->pluck('id');

        /*
        |--------------------------------------------------------------------------
        | Also check real spin-result records
        |--------------------------------------------------------------------------
        */

        $hasSpinResults = !$subCampaignIds->isEmpty()
            && DB::table('spin_results')
                ->where('spin_campaign_id', $spinCampaign->id)
                ->whereIn(
                    'spin_sub_campaign_id',
                    $subCampaignIds
                )
                ->exists();

        if ($hasSpinResults) {
            return back()->withErrors([
                'delete' =>
                    'This campaign already has spin results and cannot be deleted.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Also check case-sequence records
        |--------------------------------------------------------------------------
        */

        $hasCaseSequences = !$subCampaignIds->isEmpty()
            && DB::table('spin_case_sequences')
                ->where('spin_campaign_id', $spinCampaign->id)
                ->whereIn(
                    'spin_sub_campaign_id',
                    $subCampaignIds
                )
                ->exists();

        if ($hasCaseSequences) {
            return back()->withErrors([
                'delete' =>
                    'This campaign already has case sequence history and cannot be deleted.',
            ]);
        }

        $spinCampaign->delete();

        return redirect()
            ->route('portal.spin-campaigns.index')
            ->with(
                'success',
                'Main campaign deleted successfully.'
            );
    }

    public function storeSpecialCase(
        Request $request,
        SpinCampaign $spinCampaign
    ) {
        $validated = $request->validate([
            'spin_sub_campaign_id' => [
                'required',
                'integer',
                'exists:spin_sub_campaigns,id',
            ],
            'case_number' => [
                'required',
                'integer',
                'min:1',
            ],
            'total_discount' => [
                'required',
                'numeric',
                'min:1',
            ],
            'status' => [
                'required',
                'in:0,1',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Validate selected subcampaign
        |--------------------------------------------------------------------------
        |
        | It must belong to this main campaign.
        |
        */

        $subCampaign = SpinSubCampaign::query()
            ->whereKey(
                $validated['spin_sub_campaign_id']
            )
            ->where(
                'spin_campaign_id',
                $spinCampaign->id
            )
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Validate special-case number
        |--------------------------------------------------------------------------
        */

        if (
            (int) $validated['case_number']
            > (int) $subCampaign->total_cases
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'case_number' =>
                        "Case number cannot exceed {$subCampaign->total_cases} cases for this subcampaign.",
                ]);
        }

        SpinSpecialCase::updateOrCreate(
            [
                'spin_campaign_id' =>
                    $spinCampaign->id,

                'spin_sub_campaign_id' =>
                    $subCampaign->id,

                'case_number' =>
                    (int) $validated['case_number'],
            ],
            [
                'total_discount' =>
                    $validated['total_discount'],

                'status' =>
                    (int) $validated['status'],
            ]
        );

        return back()->with(
            'success',
            'Special case saved successfully.'
        );
    }

    public function deleteSpecialCase(
        SpinCampaign $spinCampaign,
        SpinSpecialCase $specialCase
    ) {
        /*
        |--------------------------------------------------------------------------
        | Validate main campaign ownership
        |--------------------------------------------------------------------------
        */

        $belongsToCampaign =
            (int) $specialCase->spin_campaign_id
            === (int) $spinCampaign->id;

        /*
        |--------------------------------------------------------------------------
        | Validate subcampaign ownership
        |--------------------------------------------------------------------------
        */

        $belongsToSubCampaign =
            $specialCase->spin_sub_campaign_id !== null
            && $spinCampaign->subCampaigns()
                ->whereKey(
                    $specialCase->spin_sub_campaign_id
                )
                ->exists();

        if (
            !$belongsToCampaign
            || !$belongsToSubCampaign
        ) {
            abort(404);
        }

        $specialCase->delete();

        return back()->with(
            'success',
            'Special case deleted successfully.'
        );
    }

    public function resetProgress(SpinCampaign $spinCampaign)
    {
        DB::transaction(function () use ($spinCampaign) {
            $subCampaignIds = $spinCampaign
                ->subCampaigns()
                ->pluck('id');

            if (!$subCampaignIds->isEmpty()) {
                /*
                |--------------------------------------------------------------------------
                | Delete case sequences
                |--------------------------------------------------------------------------
                */

                DB::table('spin_case_sequences')
                    ->where(
                        'spin_campaign_id',
                        $spinCampaign->id
                    )
                    ->whereIn(
                        'spin_sub_campaign_id',
                        $subCampaignIds
                    )
                    ->delete();

                /*
                |--------------------------------------------------------------------------
                | Delete spin results
                |--------------------------------------------------------------------------
                */

                DB::table('spin_results')
                    ->where(
                        'spin_campaign_id',
                        $spinCampaign->id
                    )
                    ->whereIn(
                        'spin_sub_campaign_id',
                        $subCampaignIds
                    )
                    ->delete();

                /*
                |--------------------------------------------------------------------------
                | Reset each subcampaign counter
                |--------------------------------------------------------------------------
                */

                DB::table('spin_sub_campaigns')
                    ->whereIn('id', $subCampaignIds)
                    ->update([
                        'total_spins_used' => 0,
                        'updated_at' => now(),
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Temporary parent counter reset
            |--------------------------------------------------------------------------
            |
            | Keep this while total_spins_used still exists on spin_campaigns.
            |
            */

            $spinCampaign->update([
                'total_spins_used' => 0,
            ]);
        });

        return back()->with(
            'success',
            'All subcampaign progress was reset successfully.'
        );
    }

    private function hasActiveCampaignOverlap(
        string $startDate,
        string $endDate,
        ?int $ignoreCampaignId = null
    ): bool {
        return SpinCampaign::query()
            ->where('status', 1)
            ->when(
                $ignoreCampaignId !== null,
                function ($query) use ($ignoreCampaignId) {
                    $query->where(
                        'id',
                        '!=',
                        $ignoreCampaignId
                    );
                }
            )
            ->where(function ($query) use (
                $startDate,
                $endDate
            ) {
                /*
                 * Two date ranges overlap when:
                 *
                 * existing.start_date <= new.end_date
                 * AND
                 * existing.end_date >= new.start_date
                 */

                $query
                    ->where(
                        'start_date',
                        '<=',
                        $endDate
                    )
                    ->where(
                        'end_date',
                        '>=',
                        $startDate
                    );
            })
            ->exists();
    }
}