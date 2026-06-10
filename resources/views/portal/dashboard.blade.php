@extends('portal.layouts.app')

@section('content')
    <div class="dashboard-header">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Overview of your reward portal activity.</p>
        </div>

        <div class="date-pill">
            {{ now()->format('d M Y') }}
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-card">
            <div class="dashboard-icon">👥</div>
            <h3>Total Users</h3>
            <h2>{{ $totalUsers ?? 0 }}</h2>
            <p>All registered portal and app users.</p>
        </div>

        <div class="dashboard-card">
            <div class="dashboard-icon">♙</div>
            <h3>Total Customers</h3>
            <h2>{{ $totalCustomers ?? 0 }}</h2>
            <p>Customers registered by OTP login.</p>
        </div>

        <div class="dashboard-card">
            <div class="dashboard-icon">🎁</div>
            <h3>Reward Status</h3>
            <h2>Active</h2>
            <p>Reward system is ready.</p>
        </div>

        <div class="dashboard-card">
            <div class="dashboard-icon">🔐</div>
            <h3>Auth Mode</h3>
            <h2>OTP</h2>
            <p>Mobile app login with phone OTP.</p>
        </div>
    </div>

    <div class="dashboard-panel">
        <h3>Welcome back, {{ auth()->user()->name }}</h3>
        <p>
            Your Reward Portal is connected with OTP login, customer profile,
            and slider APIs. You can continue adding reward, spin, discount,
            and transaction modules step by step.
        </p>

        <div class="quick-actions">
            <a href="{{ route('portal.profile.edit') }}" class="quick-btn primary">
                Manage Profile
            </a>

            <a href="#" class="quick-btn secondary">
                Manage Sliders
            </a>
        </div>
    </div>
@endsection