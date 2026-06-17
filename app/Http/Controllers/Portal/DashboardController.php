<?php

namespace App\Http\Controllers\Portal;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\SpinCampaign;
use App\Models\SpinResult;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::query()->count();

        $totalCustomers = User::query()
            ->where('user_type', UserType::CUSTOMER)
            ->count();

        $startOfWeek = now()
            ->startOfWeek(Carbon::MONDAY)
            ->startOfDay();

        $endOfWeek = now()
            ->endOfWeek(Carbon::SUNDAY)
            ->endOfDay();

        $weeklyResults = SpinResult::query()
            ->selectRaw('DATE(created_at) as spin_date')
            ->selectRaw('COUNT(*) as total_spins')
            ->selectRaw(
                'COALESCE(SUM(discount_percentage), 0) as total_discount'
            )
            ->whereBetween(
                'created_at',
                [
                    $startOfWeek,
                    $endOfWeek,
                ]
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get()
            ->keyBy('spin_date');

        $weeklySpinData = collect(range(0, 6))
            ->map(function ($dayOffset) use (
                $startOfWeek,
                $weeklyResults
            ) {
                $date = $startOfWeek
                    ->copy()
                    ->addDays($dayOffset);

                $result = $weeklyResults->get(
                    $date->format('Y-m-d')
                );

                return [
                    'day' => $date->format('D'),
                    'date' => $date->format('Y-m-d'),
                    'value' => (int) (
                        $result->total_spins ?? 0
                    ),
                ];
            })
            ->values()
            ->all();

        $weeklyTotalSpins = array_sum(
            array_column(
                $weeklySpinData,
                'value'
            )
        );

        $weeklyTotalDiscount = (float) SpinResult::query()
            ->whereBetween(
                'created_at',
                [
                    $startOfWeek,
                    $endOfWeek,
                ]
            )
            ->sum('discount_percentage');

        $activeCampaigns = SpinCampaign::query()
            ->where(function ($query) {
                $query
                    ->where('status', 1)
                    ->orWhere('status', 'active');
            })
            ->where(
                'start_date',
                '<=',
                now()
            )
            ->where(
                'end_date',
                '>=',
                now()
            )
            ->count();

        return view(
            'portal.dashboard',
            compact(
                'totalUsers',
                'totalCustomers',
                'weeklySpinData',
                'weeklyTotalSpins',
                'weeklyTotalDiscount',
                'activeCampaigns'
            )
        );
    }
}