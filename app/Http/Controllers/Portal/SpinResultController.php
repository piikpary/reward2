<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\SpinCampaign;
use App\Models\SpinResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SpinResultController extends Controller
{
    public function index(Request $request)
    {
        $query = SpinResult::query()
            ->select([
                'user_id',
                'spin_campaign_id',
                'spin_sub_campaign_id',
                'case_number',
            ])
            ->selectRaw('MAX(id) as id')
            ->selectRaw('COUNT(*) as spin_number')
            ->selectRaw(
                'SUM(discount_percentage) as discount_percentage'
            )
            ->selectRaw(
                'MAX(case_total_discount) as case_total_discount'
            )
            ->selectRaw(
                'MAX(created_at) as created_at'
            )
            ->with([
                'campaign:id,name',
                'subCampaign:id,name',
                'user:id,name,email',
            ]);

        if ($request->filled('campaign_id')) {
            $query->where(
                'spin_campaign_id',
                $request->campaign_id
            );
        }

        if ($request->filled('user_id')) {
            $query->where(
                'user_id',
                $request->user_id
            );
        }

        if ($request->filled('case_number')) {
            $query->where(
                'case_number',
                $request->case_number
            );
        }

        if ($request->filled('from_date')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->from_date
            );
        }

        if ($request->filled('to_date')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->to_date
            );
        }

        $cacheData = [
            'campaign_id' =>
                $request->input('campaign_id'),

            'user_id' =>
                $request->input('user_id'),

            'case_number' =>
                $request->input('case_number'),

            'from_date' =>
                $request->input('from_date'),

            'to_date' =>
                $request->input('to_date'),

            'page' => max(
                1,
                (int) $request->input('page', 1)
            ),
        ];

        $cacheKey = 'portal:spin-results:'
            . sha1(
                json_encode($cacheData)
            );

        $results = Cache::remember(
            $cacheKey,
            now()->addSeconds(20),
            function () use ($query) {
                return $query
                    ->groupBy([
                        'user_id',
                        'spin_campaign_id',
                        'spin_sub_campaign_id',
                        'case_number',
                    ])
                    ->orderByDesc('created_at')
                    ->simplePaginate(20);
            }
        );

        $results->appends(
            $request->query()
        );

        $campaigns = Cache::remember(
            'portal:spin-results:campaigns',
            now()->addMinutes(10),
            function () {
                return SpinCampaign::query()
                    ->orderBy('name')
                    ->get([
                        'id',
                        'name',
                    ]);
            }
        );

        return view(
            'portal.spin-results.index',
            compact(
                'results',
                'campaigns'
            )
        );
    }
}