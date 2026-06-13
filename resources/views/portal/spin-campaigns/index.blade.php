@extends('portal.layouts.app')

@section('content')
<div class="spin-page">
    <div class="page-header">
        <div>
            <h1>Spin Campaigns</h1>

            <p>
                Manage main campaign periods and their related subcampaign spin rules.
            </p>
        </div>

        <a
            href="{{ route('portal.spin-campaigns.create') }}"
            class="btn btn-dark"
        >
            + Add Campaign
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="summary-grid">
        <div class="summary-card">
            <span>Total Main Campaigns</span>
            <strong>{{ number_format($totalCampaigns ?? $campaigns->total()) }}</strong>
        </div>

        <div class="summary-card">
            <span>Active Campaigns</span>
            <strong>{{ number_format($activeCampaigns ?? 0) }}</strong>
        </div>

        <div class="summary-card">
            <span>Campaign Structure</span>
            <strong>Main + Subcampaigns</strong>
        </div>

        <div class="summary-card">
            <span>Reward Source</span>
            <strong>Discount List</strong>
        </div>
    </div>

    <div class="card campaign-card">
        <form
            method="GET"
            action="{{ route('portal.spin-campaigns.index') }}"
            class="campaign-filter"
        >
            <div class="search-box">
                <span>🔎</span>

                <input
                    type="text"
                    name="search"
                    value="{{ $search ?? '' }}"
                    placeholder="Search campaign name..."
                >
            </div>

            <select name="status">
                <option value="">All Status</option>

                <option
                    value="1"
                    @selected((string) ($status ?? '') === '1')
                >
                    Active
                </option>

                <option
                    value="0"
                    @selected((string) ($status ?? '') === '0')
                >
                    Inactive
                </option>
            </select>

            <button type="submit" class="btn btn-dark">
                Search
            </button>

            <a
                href="{{ route('portal.spin-campaigns.index') }}"
                class="btn btn-light"
            >
                Reset
            </a>
        </form>

        <div class="table-responsive">
            <table class="campaign-table">
                <thead>
                    <tr>
                        <th>Campaign</th>
                        <th>Period</th>
                        <th>Subcampaigns</th>
                        <th>Combined Cases</th>
                        <th>Spin Quota</th>
                        <th>Progress</th>
                        <th>Special Cases</th>
                        <th>Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($campaigns as $campaign)
                        @php
                            /*
                            |--------------------------------------------------------------------------
                            | Aggregate values from subcampaigns
                            |--------------------------------------------------------------------------
                            */

                            $subCampaigns = $campaign->subCampaigns;

                            $combinedCases = $subCampaigns->sum(function ($subCampaign) {
                                return (int) $subCampaign->total_cases;
                            });

                            $totalAllowedSpins = $subCampaigns->sum(function ($subCampaign) {
                                return
                                    (int) $subCampaign->total_cases
                                    * (int) $subCampaign->spins_per_case;
                            });

                            $totalUsedSpins = $subCampaigns->sum(function ($subCampaign) {
                                return (int) $subCampaign->total_spins_used;
                            });

                            $remainingSpins = max(
                                0,
                                $totalAllowedSpins - $totalUsedSpins
                            );

                            $progressPercent = $totalAllowedSpins > 0
                                ? min(
                                    100,
                                    round(
                                        ($totalUsedSpins / $totalAllowedSpins) * 100,
                                        2
                                    )
                                )
                                : 0;

                            $specialCasesCount = $subCampaigns->sum(function ($subCampaign) {
                                return (int) ($subCampaign->special_cases_count ?? 0);
                            });

                            $isActive =
                                (string) $campaign->status === '1'
                                || $campaign->status === 'active';
                        @endphp

                        <tr>
                            <td>
                                <div class="campaign-name">
                                    <div class="campaign-icon">
                                        🎯
                                    </div>

                                    <div>
                                        <strong>
                                            {{ $campaign->name }}
                                        </strong>

                                        <small>
                                            {{ $campaign->description ?: 'No description' }}
                                        </small>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="date-range">
                                    <span>
                                        {{ $campaign->start_date?->format('d M Y') }}
                                    </span>

                                    <small>to</small>

                                    <span>
                                        {{ $campaign->end_date?->format('d M Y') }}
                                    </span>
                                </div>
                            </td>

                            <td>
                                <span class="subcampaign-pill">
                                    {{ number_format($campaign->sub_campaigns_count ?? 0) }}
                                    rules
                                </span>
                            </td>

                            <td>
                                <strong>
                                    {{ number_format($combinedCases) }}
                                </strong>

                                <small>
                                    Combined cases
                                </small>
                            </td>

                            <td>
                                <strong>
                                    {{ number_format($totalAllowedSpins) }}
                                </strong>

                                <small>
                                    {{ number_format($remainingSpins) }} remaining
                                </small>
                            </td>

                            <td>
                                <div class="progress-info">
                                    <div class="progress-top">
                                        <span>
                                            {{ number_format($totalUsedSpins) }}
                                            /
                                            {{ number_format($totalAllowedSpins) }}
                                        </span>

                                        <strong>
                                            {{ number_format($progressPercent, 1) }}%
                                        </strong>
                                    </div>

                                    <div class="progress-bar">
                                        <div
                                            style="width: {{ $progressPercent }}%"
                                        ></div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="special-pill">
                                    {{ number_format($specialCasesCount) }}
                                    cases
                                </span>
                            </td>

                            <td>
                                @if($isActive)
                                    <span class="badge active">
                                        Active
                                    </span>
                                @else
                                    <span class="badge inactive">
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            <td>
                                <div class="action-group">
                                    <a
                                        href="{{ route(
                                            'portal.spin-campaigns.sub-campaigns.index',
                                            $campaign
                                        ) }}"
                                        class="btn btn-sm btn-purple"
                                    >
                                        Subcampaigns
                                    </a>

                                    <a
                                        href="{{ route(
                                            'portal.spin-campaigns.show',
                                            $campaign
                                        ) }}"
                                        class="btn btn-sm btn-light"
                                    >
                                        View
                                    </a>

                                    <a
                                        href="{{ route(
                                            'portal.spin-campaigns.edit',
                                            $campaign
                                        ) }}"
                                        class="btn btn-sm btn-dark"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        action="{{ route(
                                            'portal.spin-campaigns.destroy',
                                            $campaign
                                        ) }}"
                                        method="POST"
                                        onsubmit="return confirm(
                                            'Delete this main campaign and all related subcampaigns?'
                                        )"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-danger"
                                        >
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <div>🎯</div>

                                    <h3>No spin campaign found</h3>

                                    <p>
                                        Create a main campaign period, then add
                                        subcampaign spin rules inside it.
                                    </p>

                                    <a
                                        href="{{ route('portal.spin-campaigns.create') }}"
                                        class="btn btn-dark"
                                    >
                                        Add Campaign
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($campaigns->hasPages())
            <div class="pagination-wrap">
                {{ $campaigns->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    .spin-page {
        max-width: 1600px;
        margin: 0 auto;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 18px;
        margin-bottom: 22px;
    }

    .page-header h1 {
        margin: 0;
        font-size: 34px;
        font-weight: 900;
        letter-spacing: -0.04em;
        color: #071629;
    }

    .page-header p {
        margin: 8px 0 0;
        color: #6b7280;
        font-size: 15px;
    }

    .alert {
        padding: 14px 17px;
        margin-bottom: 18px;
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

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .summary-card {
        padding: 20px;
        border: 1px solid #eeeeee;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
    }

    .summary-card span {
        display: block;
        margin-bottom: 8px;
        color: #6b7280;
        font-size: 13px;
        font-weight: 700;
    }

    .summary-card strong {
        display: block;
        color: #0d1b2a;
        font-size: 22px;
        font-weight: 900;
    }

    .campaign-card {
        padding: 22px;
        border-radius: 22px;
        overflow: visible;
    }

    .campaign-filter {
        display: grid;
        grid-template-columns: 1fr 180px auto auto;
        gap: 12px;
        align-items: center;
        margin-bottom: 20px;
    }

    .search-box {
        height: 46px;
        padding: 0 14px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #f9fafb;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .search-box input {
        width: 100%;
        border: 0;
        outline: none;
        background: transparent;
        font-size: 14px;
    }

    .campaign-filter select {
        height: 46px;
        padding: 0 14px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #ffffff;
        outline: none;
    }

    .btn {
        padding: 12px 16px;
        border: 0;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: inherit;
        cursor: pointer;
        text-decoration: none;
        font-size: 14px;
        font-weight: 800;
        white-space: nowrap;
        transition: 0.2s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    .btn-dark {
        background: #0d1b2a;
        color: #ffffff;
    }

    .btn-light {
        background: #f3f4f6;
        color: #111827;
    }

    .btn-purple {
        background: #6d28d9;
        color: #ffffff;
    }

    .btn-danger {
        background: #dc2626;
        color: #ffffff;
    }

    .btn-sm {
        padding: 8px 11px;
        border-radius: 10px;
        font-size: 12px;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .campaign-table {
        width: 100%;
        min-width: 1450px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .campaign-table thead th {
        padding: 15px 14px;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
        color: #374151;
        font-size: 11px;
        font-weight: 900;
        text-align: left;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        white-space: nowrap;
    }

    .campaign-table thead th:first-child {
        border-top-left-radius: 14px;
    }

    .campaign-table thead th:last-child {
        border-top-right-radius: 14px;
    }

    .campaign-table tbody td {
        padding: 18px 14px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .campaign-table tbody tr:hover {
        background: #fcfcfd;
    }

    .campaign-name {
        min-width: 250px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .campaign-icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: #eef2ff;
        display: grid;
        place-items: center;
        font-size: 18px;
        flex: 0 0 auto;
    }

    .campaign-name strong,
    .campaign-table td strong {
        display: block;
        color: #111827;
        font-size: 14px;
        font-weight: 900;
    }

    .campaign-name small,
    .campaign-table td small {
        display: block;
        margin-top: 4px;
        color: #6b7280;
        font-size: 12px;
        line-height: 1.45;
    }

    .date-range span {
        display: block;
        color: #111827;
        font-size: 13px;
        font-weight: 800;
        white-space: nowrap;
    }

    .date-range small {
        color: #6b7280;
        font-size: 11px;
    }

    .subcampaign-pill,
    .special-pill {
        padding: 7px 11px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }

    .subcampaign-pill {
        background: #f3e8ff;
        color: #7e22ce;
    }

    .special-pill {
        background: #fff7ed;
        color: #c2410c;
    }

    .progress-info {
        min-width: 190px;
    }

    .progress-top {
        margin-bottom: 8px;
        display: flex;
        justify-content: space-between;
        gap: 10px;
        font-size: 12px;
    }

    .progress-top span {
        color: #6b7280;
        font-weight: 700;
    }

    .progress-top strong {
        color: #0d1b2a;
        font-weight: 900;
    }

    .progress-bar {
        height: 9px;
        overflow: hidden;
        border-radius: 999px;
        background: #e5e7eb;
    }

    .progress-bar div {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #0d1b2a, #334e68);
    }

    .badge {
        padding: 7px 11px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        font-size: 12px;
        font-weight: 900;
    }

    .badge.active {
        background: #dcfce7;
        color: #047857;
    }

    .badge.inactive {
        background: #fee2e2;
        color: #b91c1c;
    }

    .action-group {
        min-width: 300px;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 7px;
    }

    .action-group form {
        margin: 0;
    }

    .text-right {
        text-align: right !important;
    }

    .empty-state {
        padding: 50px 20px;
        text-align: center;
    }

    .empty-state > div {
        font-size: 40px;
    }

    .empty-state h3 {
        margin: 12px 0 6px;
        font-size: 20px;
        font-weight: 900;
    }

    .empty-state p {
        margin: 0 0 16px;
        color: #6b7280;
    }

    .pagination-wrap {
        margin-top: 18px;
    }

    .pagination-wrap svg,
    nav[role="navigation"] svg {
        width: 18px !important;
        height: 18px !important;
    }

    @media (max-width: 1100px) {
        .summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .campaign-filter {
            grid-template-columns: 1fr 160px;
        }
    }

    @media (max-width: 640px) {
        .page-header {
            flex-direction: column;
            align-items: stretch;
        }

        .summary-grid,
        .campaign-filter {
            grid-template-columns: 1fr;
        }

        .campaign-card {
            padding: 14px;
        }
    }
</style>
@endsection