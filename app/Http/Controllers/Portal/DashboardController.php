<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalCustomers = User::where('user_type', \App\Enums\UserType::CUSTOMER)->count();

        return view('portal.dashboard', compact('totalUsers', 'totalCustomers'));
    }
}