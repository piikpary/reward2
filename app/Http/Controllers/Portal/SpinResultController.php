<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\SpinCampaign;
use App\Models\SpinResult;
use Illuminate\Http\Request;

class SpinResultController extends Controller
{
    public function index(Request $request)
    {
        $query = SpinResult::query()
            ->with([
                'campaign:id,name',
                'subCampaign:id,name',
                'user:id,name,email',
            ])
            ->latest();

        if ($request->filled('campaign_id')) {
            $query->where('spin_campaign_id', $request->campaign_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('case_number')) {
            $query->where('case_number', $request->case_number);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $results = $query
            ->paginate(20)
            ->withQueryString();

        $campaigns = SpinCampaign::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('portal.spin-results.index', compact(
            'results',
            'campaigns'
        ));
    }
}