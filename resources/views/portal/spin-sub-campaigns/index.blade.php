@extends('portal.layouts.app')

@section('content')
<style>
    .sub-page {
        padding: 8px 0 35px;
    }

    .sub-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        margin-bottom: 22px;
    }

    .sub-title {
        margin: 0;
        color: #071629;
        font-size: 32px;
        font-weight: 900;
        letter-spacing: -0.7px;
    }

    .sub-description {
        margin: 7px 0 0;
        color: #64748b;
        font-size: 15px;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .back-btn,
    .add-btn {
        min-height: 44px;
        padding: 0 18px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 850;
        transition: 0.2s ease;
    }

    .back-btn {
        border: 1px solid #dfe5ec;
        background: #ffffff;
        color: #172033;
    }

    .add-btn {
        border: 1px solid #0b1b2b;
        background: #0b1b2b;
        color: #ffffff;
    }

    .back-btn:hover,
    .add-btn:hover {
        transform: translateY(-1px);
    }

    .campaign-banner {
        margin-bottom: 22px;
        padding: 22px 24px;
        border: 1px solid #dfe7ef;
        border-radius: 20px;
        background:
            radial-gradient(
                circle at top right,
                rgba(111, 45, 189, 0.10),
                transparent 28%
            ),
            linear-gradient(135deg, #ffffff, #f8fafc);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        box-shadow: 0 12px 34px rgba(15, 23, 42, 0.05);
    }

    .campaign-banner h2 {
        margin: 0;
        color: #071629;
        font-size: 22px;
        font-weight: 900;
    }

    .campaign-banner p {
        margin: 7px 0 0;
        color: #64748b;
        font-size: 14px;
    }

    .period-badge {
        padding: 11px 16px;
        border: 1px solid #dce4ec;
        border-radius: 999px;
        background: #ffffff;
        color: #172033;
        font-size: 13px;
        font-weight: 850;
        white-space: nowrap;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 15px;
        margin-bottom: 22px;
    }

    .stat-card {
        padding: 19px;
        border: 1px solid #e5eaf0;
        border-radius: 17px;
        background: #ffffff;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.04);
    }
 

    .stat-card span {
        display: block;
        margin-bottom: 8px;
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .stat-card strong {
        color: #071629;
        font-size: 23px;
        font-weight: 900;
    }

    .alert-box {
        margin-bottom: 18px;
        padding: 14px 17px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
    }

    .alert-success {
        border: 1px solid #bbf7d0;
        background: #f0fdf4;
        color: #166534;
    }

    .alert-error {
        border: 1px solid #fecaca;
        background: #fef2f2;
        color: #991b1b;
    }

    .table-card {
        overflow: hidden;
        border: 1px solid #e5eaf0;
        border-radius: 20px;
        background: #ffffff;
        box-shadow: 0 16px 42px rgba(15, 23, 42, 0.06);
    }

    .table-card-header {
        padding: 21px 24px;
        border-bottom: 1px solid #edf1f5;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
    }

    .table-card-header h3 {
        margin: 0;
        color: #071629;
        font-size: 19px;
        font-weight: 900;
    }

    .table-card-header p {
        margin: 5px 0 0;
        color: #64748b;
        font-size: 13px;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .sub-table {
        width: 100%;
        min-width: 1250px;
        border-collapse: collapse;
    }

    .sub-table th {
        padding: 15px 17px;
        border-bottom: 1px solid #e9edf2;
        background: #f8fafc;
        color: #475569;
        font-size: 11px;
        font-weight: 900;
        text-align: left;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }

    .sub-table td {
        padding: 17px;
        border-bottom: 1px solid #edf1f5;
        color: #172033;
        font-size: 14px;
        vertical-align: middle;
    }

    .sub-table tbody tr:hover {
        background: #fbfcfd;
    }

    .rule-name {
        min-width: 180px;
    }

    .rule-name strong {
        display: block;
        color: #071629;
        font-size: 14px;
        font-weight: 900;
    }

    .rule-name small {
        display: block;
        max-width: 220px;
        margin-top: 5px;
        overflow: hidden;
        color: #8491a3;
        font-size: 12px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .number-value {
        color: #071629;
        font-weight: 850;
    }

    .discount-badge,
    .priority-badge,
    .status-badge,
    .special-spin-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 29px;
        padding: 0 11px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 850;
        white-space: nowrap;
    }

    .discount-badge {
        background: #f3e8ff;
        color: #7e22ce;
    }

    .priority-badge {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .status-active {
        background: #dcfce7;
        color: #15803d;
    }

    .status-inactive {
        background: #f1f5f9;
        color: #64748b;
    }

    .special-spin-active {
        background: #ede9fe;
        color: #6d28d9;
    }

    

    .special-spin-disabled {
        background: #f1f5f9;
        color: #64748b;
    }

  

    .progress-cell {
        min-width: 150px;
    }

    .progress-info {
        margin-bottom: 7px;
        display: flex;
        justify-content: space-between;
        gap: 10px;
        color: #64748b;
        font-size: 11px;
        font-weight: 750;
    }

    .progress-track {
        height: 7px;
        overflow: hidden;
        border-radius: 999px;
        background: #e9eef4;
    }

    .progress-bar {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #0b1b2b, #334e68);
    }

    .action-group {
        display: flex;
        align-items: center;
        gap: 7px;
        white-space: nowrap;
    }

    .edit-btn,
    .delete-btn {
        min-height: 35px;
        padding: 0 13px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 850;
        text-decoration: none;
        cursor: pointer;
    }

    .edit-btn {
        border: 1px solid #0b1b2b;
        background: #0b1b2b;
        color: #ffffff;
    }

    .delete-btn {
        border: 1px solid #fecaca;
        background: #fef2f2;
        color: #dc2626;
    }

    .empty-state {
        padding: 55px 20px !important;
        text-align: center;
    }

    .empty-icon {
        width: 58px;
        height: 58px;
        margin: 0 auto 14px;
        border-radius: 17px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 25px;
    }

    .empty-state h3 {
        margin: 0;
        color: #071629;
        font-size: 18px;
        font-weight: 900;
    }

    .empty-state p {
        margin: 7px 0 18px;
        color: #64748b;
    }

    .pagination-wrap {
        padding: 18px 22px;
    }

    .pagination-wrap svg,
    nav[role="navigation"] svg {
        width: 18px !important;
        height: 18px !important;
    }

    @media (max-width: 1050px) {
        .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 700px) {
        .sub-header,
        .campaign-banner,
        .table-card-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .sub-title {
            font-size: 27px;
        }

        .header-actions,
        .back-btn,
        .add-btn {
            width: 100%;
        }
    }
</style>

@php
    $totalSubCampaigns = $campaign
        ->subCampaigns()
        ->count();

    $activeSubCampaigns = $campaign
        ->subCampaigns()
        ->where('status', 'active')
        ->count();

    $totalCases = $campaign
        ->subCampaigns()
        ->sum('total_cases');

    $totalAllowedSpins = $campaign
        ->subCampaigns()
        ->get()
        ->sum(function ($item) {
            return
                (int) $item->total_cases
                * (int) $item->spins_per_case;
        });
@endphp

<div class="sub-page">
    <div class="sub-header">
        <div>
            <h1 class="sub-title">Subcampaigns</h1>

            <p class="sub-description">
                Manage case groups and spin rules inside the selected main campaign.
            </p>
        </div>

        <div class="header-actions">
            <a
                href="{{ route('portal.spin-campaigns.index') }}"
                class="back-btn"
            >
                ← Main Campaigns
            </a>

            <a
                href="{{ route(
                    'portal.spin-campaigns.sub-campaigns.create',
                    $campaign
                ) }}"
                class="add-btn"
            >
                + Add Subcampaign
            </a>
        </div>
    </div>

    <div class="campaign-banner">
        <div>
            <h2>{{ $campaign->name }}</h2>

            <p>
                All subcampaigns below use this main campaign period.
            </p>
        </div>

        <div class="period-badge">
            {{ \Carbon\Carbon::parse(
                $campaign->start_date
            )->format('d M Y') }}

            —

            {{ \Carbon\Carbon::parse(
                $campaign->end_date
            )->format('d M Y') }}
        </div>
    </div>

    @if(session('success'))
        <div class="alert-box alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert-box alert-error">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="stats-grid">
        <div class="stat-card">
            <span>Total Subcampaigns</span>

            <strong>
                {{ number_format($totalSubCampaigns) }}
            </strong>
        </div>

        <div class="stat-card">
            <span>Active Rules</span>

            <strong>
                {{ number_format($activeSubCampaigns) }}
            </strong>
        </div>

        <div class="stat-card">
            <span>Combined Cases</span>

            <strong>
                {{ number_format($totalCases) }}
            </strong>
        </div>

        <div class="stat-card">
            <span>Total Spin Quota</span>

            <strong>
                {{ number_format($totalAllowedSpins) }}
            </strong>
        </div>
    </div>

    <div class="table-card">
        <div class="table-card-header">
            <div>
                <h3>Subcampaign Rules</h3>
            </div>
        </div>

        <div class="table-responsive">
            <table class="sub-table">
                <thead>
                    <tr>
                        <th>Subcampaign</th>
                        <th>Cases</th>
                        <th>Spin Rule</th>
                        <th>Normal Total</th>
                        <th>Special Spin</th>
                        <th>Spin Quota</th>
                        <th>Usage Progress</th>
                        <th>Remaining</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($subCampaigns as $subCampaign)
                        @php
                            $totalSpins =
                                (int) $subCampaign->total_cases
                                * (int) $subCampaign->spins_per_case;

                            $usedSpins =
                                (int) $subCampaign->total_spins_used;

                            $remainingSpins = max(
                                0,
                                $totalSpins - $usedSpins
                            );

                            $progress = $totalSpins > 0
                                ? min(
                                    100,
                                    ($usedSpins / $totalSpins) * 100
                                )
                                : 0;

                            /*
                             * Hidden special-spin configuration.
                             *
                             * Do not display the assigned position.
                             */
                           $hasSpecialRewards = $subCampaign
                            ->specialRewards
                            ->isNotEmpty();

                        @endphp

                        <tr>
                            <td>
                                <div class="rule-name">
                                    <strong>
                                        {{ $subCampaign->name }}
                                    </strong>

                                    <small>
                                        {{
                                            $subCampaign->description
                                                ?: 'No description'
                                        }}
                                    </small>
                                </div>
                            </td>

                            <td>
                                <span class="number-value">
                                    {{
                                        number_format(
                                            $subCampaign->total_cases
                                        )
                                    }}
                                </span>
                            </td>

                            <td>
                                <span class="number-value">
                                    {{ $subCampaign->spins_per_case }}
                                    spins / case
                                </span>
                            </td>

                            <td>
                                <span class="discount-badge">
                                    {{
                                        number_format(
                                            $subCampaign
                                                ->normal_discount_total,
                                            2
                                        )
                                    }}%
                                </span>
                            </td>

                      <td>
    @if($hasSpecialRewards)
        <span
            class="
                special-spin-badge
                special-spin-active
            "
        >
            Enabled
        </span>
    @else
        <span
            class="
                special-spin-badge
                special-spin-disabled
            "
        >
            Disabled
        </span>
    @endif
</td>
                            <td>
                                <span class="number-value">
                                    {{ number_format($totalSpins) }}
                                </span>
                            </td>

                            <td>
                                <div class="progress-cell">
                                    <div class="progress-info">
                                        <span>
                                            {{ number_format($usedSpins) }}
                                            /
                                            {{ number_format($totalSpins) }}
                                        </span>

                                        <span>
                                            {{
                                                number_format(
                                                    $progress,
                                                    1
                                                )
                                            }}%
                                        </span>
                                    </div>

                                    <div class="progress-track">
                                        <div
                                            class="progress-bar"
                                            style="width: {{ $progress }}%"
                                        ></div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="number-value">
                                    {{
                                        number_format(
                                            $remainingSpins
                                        )
                                    }}
                                </span>
                            </td>

                            <td>
                                <span class="priority-badge">
                                    {{ $subCampaign->priority }}
                                </span>
                            </td>

                            <td>
                                <span
                                    class="status-badge {{
                                        $subCampaign->status === 'active'
                                            ? 'status-active'
                                            : 'status-inactive'
                                    }}"
                                >
                                    {{
                                        ucfirst(
                                            $subCampaign->status
                                        )
                                    }}
                                </span>
                            </td>

                            <td>
                                <div class="action-group">
                                    <a
                                        href="{{ route(
                                            'portal.spin-campaigns.sub-campaigns.edit',
                                            [
                                                $campaign,
                                                $subCampaign,
                                            ]
                                        ) }}"
                                        class="edit-btn"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'portal.spin-campaigns.sub-campaigns.destroy',
                                            [
                                                $campaign,
                                                $subCampaign,
                                            ]
                                        ) }}"
                                        onsubmit="return confirm('Delete this subcampaign?');"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="delete-btn"
                                        >
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="11"
                                class="empty-state"
                            >
                                <div class="empty-icon">
                                    🎯
                                </div>

                                <h3>
                                    No subcampaigns found
                                </h3>

                                <p>
                                    Add the first rule for this
                                    main campaign.
                                </p>

                                <a
                                    href="{{ route(
                                        'portal.spin-campaigns.sub-campaigns.create',
                                        $campaign
                                    ) }}"
                                    class="add-btn"
                                >
                                    + Add Subcampaign
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($subCampaigns->hasPages())
            <div class="pagination-wrap">
                {{ $subCampaigns->links() }}
            </div>
        @endif
    </div>
</div>
@endsection