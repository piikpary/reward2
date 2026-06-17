@extends('portal.layouts.app')

@section('content')
<style>
    .dashboard-page {
        padding: 4px 0 36px;
    }

    .dashboard-header {
        margin-bottom: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 18px;
    }

    .page-title {
        margin: 0;
        color: #071629;
        font-size: 32px;
        font-weight: 900;
        letter-spacing: -0.7px;
    }

    .page-subtitle {
        margin: 7px 0 0;
        color: #64748b;
        font-size: 15px;
    }

    .date-pill {
        min-height: 44px;
        padding: 0 17px;
        border: 1px solid #dfe5ec;
        border-radius: 12px;
        background: #ffffff;
        color: #172033;
        display: inline-flex;
        align-items: center;
        gap: 9px;
        font-size: 13px;
        font-weight: 850;
        white-space: nowrap;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.05);
    }

    .dashboard-grid {
        margin-bottom: 22px;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .dashboard-card {
        position: relative;
        min-height: 160px;
        padding: 24px;
        overflow: hidden;
        border: 1px solid #e5eaf0;
        border-radius: 20px;
        background: #ffffff;
        box-shadow: 0 14px 36px rgba(15, 23, 42, 0.06);
    }

    .dashboard-card::after {
        content: "";
        position: absolute;
        top: -45px;
        right: -35px;
        width: 130px;
        height: 130px;
        border-radius: 50%;
        background: rgba(11, 27, 43, 0.04);
    }

    .card-top {
        position: relative;
        z-index: 1;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 15px;
    }

    .card-label {
        margin: 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .dashboard-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: #0b1b2b;
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        box-shadow: 0 10px 22px rgba(11, 27, 43, 0.20);
    }

    .card-value {
        position: relative;
        z-index: 1;
        margin: 22px 0 8px;
        color: #071629;
        font-size: 38px;
        line-height: 1;
        font-weight: 900;
    }

    .card-description {
        position: relative;
        z-index: 1;
        margin: 0;
        color: #7b8798;
        font-size: 13px;
    }

    .chart-card {
        overflow: hidden;
        border: 1px solid #e5eaf0;
        border-radius: 22px;
        background: #ffffff;
        box-shadow: 0 16px 42px rgba(15, 23, 42, 0.06);
    }

    .chart-card-header {
        padding: 23px 26px;
        border-bottom: 1px solid #edf1f5;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
        background: linear-gradient(135deg, #ffffff, #f8fafc);
    }

    .chart-card-header h2 {
        margin: 0;
        color: #071629;
        font-size: 21px;
        font-weight: 900;
    }

    .chart-card-header p {
        margin: 6px 0 0;
        color: #64748b;
        font-size: 13px;
    }

    .chart-badge {
        min-height: 34px;
        padding: 0 13px;
        border-radius: 999px;
        background: #eef2ff;
        color: #4338ca;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 850;
        white-space: nowrap;
    }

    .chart-body {
        padding: 25px 26px 28px;
    }

    .chart-stats {
        margin-bottom: 26px;
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .chart-stat {
        padding: 16px;
        border: 1px solid #e8edf3;
        border-radius: 15px;
        background: #f8fafc;
    }

    .chart-stat span {
        display: block;
        margin-bottom: 8px;
        color: #718096;
        font-size: 12px;
        font-weight: 750;
    }

    .chart-stat strong {
        display: block;
        color: #071629;
        font-size: 22px;
        font-weight: 900;
    }

    .bar-chart-box {
        padding: 22px 22px 16px;
        border: 1px solid #edf1f5;
        border-radius: 18px;
        background:
            linear-gradient(
                to top,
                transparent 24%,
                #edf1f5 25%,
                transparent 26%,
                transparent 49%,
                #edf1f5 50%,
                transparent 51%,
                transparent 74%,
                #edf1f5 75%,
                transparent 76%
            ),
            linear-gradient(180deg, #ffffff, #fafbfc);
    }

    .bar-chart {
        height: 300px;
        display: grid;
        grid-template-columns: repeat(7, minmax(45px, 1fr));
        align-items: end;
        gap: 20px;
    }

    .bar-item {
        height: 100%;
        min-width: 0;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        align-items: center;
    }

    .bar-value {
        margin-bottom: 8px;
        color: #172033;
        font-size: 11px;
        font-weight: 850;
    }

    .bar-track {
        width: 100%;
        max-width: 58px;
        height: 230px;
        border-radius: 12px 12px 5px 5px;
        background: rgba(11, 27, 43, 0.06);
        display: flex;
        align-items: flex-end;
        overflow: hidden;
    }

    .bar-fill {
        width: 100%;
        border-radius: 12px 12px 5px 5px;
        background: linear-gradient(180deg, #294b6d, #0b1b2b);
        transition: height 0.3s ease;
    }

    .bar-label {
        margin-top: 11px;
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
    }

    .chart-note {
        margin: 17px 0 0;
        color: #8491a3;
        font-size: 12px;
        text-align: center;
    }

    .empty-chart {
        height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #8491a3;
        font-size: 14px;
        font-weight: 700;
    }

    @media (max-width: 900px) {
        .dashboard-grid,
        .chart-stats {
            grid-template-columns: 1fr;
        }

        .dashboard-header,
        .chart-card-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .page-title {
            font-size: 28px;
        }

        .chart-body,
        .chart-card-header {
            padding-left: 20px;
            padding-right: 20px;
        }

        .bar-chart {
            gap: 12px;
        }
    }

    @media (max-width: 620px) {
        .date-pill {
            width: 100%;
            justify-content: center;
        }

        .bar-chart-box {
            padding-left: 12px;
            padding-right: 12px;
            overflow-x: auto;
        }

        .bar-chart {
            min-width: 560px;
        }

        .dashboard-card {
            min-height: auto;
        }
    }
</style>

@php
    $weeklySpinData = $weeklySpinData ?? [];

    $weeklyMaxSpin = max(
        (int) (
            collect($weeklySpinData)
                ->pluck('value')
                ->max() ?? 0
        ),
        1
    );

    $hasWeeklySpinData = collect($weeklySpinData)
        ->sum('value') > 0;
@endphp

<div class="dashboard-page">
    <div class="dashboard-header">
        <div>
            <h1 class="page-title">
                Dashboard
            </h1>

            <p class="page-subtitle">
                Overview of reward portal users and spin activity.
            </p>
        </div>

        <div class="date-pill">
            <span>📅</span>
            {{ now()->format('d M Y') }}
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-card">
            <div class="card-top">
                <p class="card-label">
                    Total Users
                </p>

                <div class="dashboard-icon">
                    👥
                </div>
            </div>

            <div class="card-value">
                {{ number_format($totalUsers ?? 0) }}
            </div>

            <p class="card-description">
                All registered portal and mobile application users.
            </p>
        </div>

        <div class="dashboard-card">
            <div class="card-top">
                <p class="card-label">
                    Total Customers
                </p>

                <div class="dashboard-icon">
                    ♙
                </div>
            </div>

            <div class="card-value">
                {{ number_format($totalCustomers ?? 0) }}
            </div>

            <p class="card-description">
                Customers successfully registered using OTP login.
            </p>
        </div>
    </div>

    <div class="chart-card">
        <div class="chart-card-header">
            <div>
                <h2>
                    Weekly Spin Activity
                </h2>

                <p>
                    Number of completed spins from Monday to Sunday.
                </p>
            </div>

            <div class="chart-badge">
                Current Week
            </div>
        </div>

        <div class="chart-body">
            <div class="chart-stats">
                <div class="chart-stat">
                    <span>Total Spins</span>

                    <strong>
                        {{
                            number_format(
                                $weeklyTotalSpins ?? 0
                            )
                        }}
                    </strong>
                </div>

                <div class="chart-stat">
                    <span>Total Discount</span>

                    <strong>
                        {{
                            number_format(
                                (float) (
                                    $weeklyTotalDiscount ?? 0
                                ),
                                2
                            )
                        }}%
                    </strong>
                </div>

                <div class="chart-stat">
                    <span>Active Campaigns</span>

                    <strong>
                        {{
                            number_format(
                                $activeCampaigns ?? 0
                            )
                        }}
                    </strong>
                </div>
            </div>

            <div class="bar-chart-box">
                @if($hasWeeklySpinData)
                    <div class="bar-chart">
                        @foreach($weeklySpinData as $item)
                            @php
                                $spinValue =
                                    (int) ($item['value'] ?? 0);

                                $barHeight = $spinValue > 0
                                    ? max(
                                        4,
                                        (
                                            $spinValue
                                            / $weeklyMaxSpin
                                        ) * 100
                                    )
                                    : 0;
                            @endphp

                            <div class="bar-item">
                                <span class="bar-value">
                                    {{
                                        number_format(
                                            $spinValue
                                        )
                                    }}
                                </span>

                                <div class="bar-track">
                                    <div
                                        class="bar-fill"
                                        style="height: {{ $barHeight }}%;"
                                    ></div>
                                </div>

                                <span class="bar-label">
                                    {{ $item['day'] ?? '-' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-chart">
                        No spin activity recorded for this week.
                    </div>
                @endif
            </div>

            <p class="chart-note">
                Bar height represents the number of spins completed each day.
            </p>
        </div>
    </div>
</div>
@endsection