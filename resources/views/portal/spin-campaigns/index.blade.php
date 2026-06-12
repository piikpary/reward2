@extends('portal.layouts.app')

@section('content')
<div class="spin-page">
    <div class="page-header">
        <div>
            <h1>Spin Campaigns</h1>
            <p>Manage monthly case-based spin rules, total cases, spin quota, and special cases.</p>
        </div>

        <a href="{{ route('portal.spin-campaigns.create') }}" class="btn btn-dark">
            + Add Campaign
        </a>
    </div>

    @if(session('success'))
        <div class="success">{{ session('success') }}</div>
    @endif

    <div class="summary-grid">
        <div class="summary-card">
            <span>Total Campaigns</span>
            <strong>{{ $campaigns->total() }}</strong>
        </div>

        <div class="summary-card">
            <span>Active Rules</span>
            <strong>{{ $campaigns->where('status', 1)->count() }}</strong>
        </div>

        <div class="summary-card">
            <span>Normal Rule</span>
            <strong>4 Spins = 30%</strong>
        </div>

        <div class="summary-card">
            <span>Reward Source</span>
            <strong>Discount List</strong>
        </div>
    </div>

    <div class="card campaign-card">
        <form method="GET" class="campaign-filter">
            <div class="search-box">
                <span>🔎</span>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search campaign name...">
            </div>

            <select name="status">
                <option value="">All Status</option>
                <option value="1" @selected((string)$status === '1')>Active</option>
                <option value="0" @selected((string)$status === '0')>Inactive</option>
            </select>

            <button type="submit" class="btn btn-dark">Search</button>
            <a href="{{ route('portal.spin-campaigns.index') }}" class="btn btn-light">Reset</a>
        </form>

        <div class="table-responsive">
            <table class="campaign-table">
                <thead>
                    <tr>
                        <th>Campaign</th>
                        <th>Period</th>
                        <th>Cases</th>
                        <th>Spin Rule</th>
                        <th>Progress</th>
                        <th>Special</th>
                        <th>Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($campaigns as $campaign)
                        @php
                            $totalAllowed = $campaign->total_allowed_spins ?? (($campaign->total_cases ?? 0) * ($campaign->spins_per_case ?? 0));
                            $used = $campaign->total_spins_used ?? 0;
                            $percent = $totalAllowed > 0 ? round(($used / $totalAllowed) * 100, 2) : 0;
                        @endphp

                        <tr>
                            <td>
                                <div class="campaign-name">
                                    <div class="campaign-icon">🎯</div>
                                    <div>
                                        <strong>{{ $campaign->name }}</strong>
                                        <small>{{ $campaign->description ?: 'No description' }}</small>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="date-range">
                                    <span>{{ $campaign->start_date?->format('Y-m-d') }}</span>
                                    <small>to</small>
                                    <span>{{ $campaign->end_date?->format('Y-m-d') }}</span>
                                </div>
                            </td>

                            <td>
                                <strong>{{ number_format($campaign->total_cases) }}</strong>
                                <small>cases</small>
                            </td>

                            <td>
                                <strong>{{ $campaign->spins_per_case }} spins / case</strong>
                                <small>Normal total: {{ $campaign->normal_discount_total }}%</small>
                            </td>

                            <td>
                                <div class="progress-info">
                                    <div class="progress-top">
                                        <span>{{ number_format($used) }} / {{ number_format($totalAllowed) }}</span>
                                        <strong>{{ $percent }}%</strong>
                                    </div>
                                    <div class="progress-bar">
                                        <div style="width: {{ min($percent, 100) }}%"></div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="special-pill">
                                    {{ $campaign->special_cases_count }} cases
                                </span>
                            </td>

                            <td>
                                @if((string)$campaign->status === '1' || $campaign->status === 'active')
                                    <span class="badge active">Active</span>
                                @else
                                    <span class="badge inactive">Inactive</span>
                                @endif
                            </td>

                            <td>
                                <div class="action-group">
                                    <a href="{{ route('portal.spin-campaigns.show', $campaign) }}" class="btn btn-sm btn-light">View</a>
                                    <a href="{{ route('portal.spin-campaigns.edit', $campaign) }}" class="btn btn-sm btn-dark">Edit</a>

                                    <form action="{{ route('portal.spin-campaigns.destroy', $campaign) }}" method="POST" onsubmit="return confirm('Delete this campaign?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div>🎯</div>
                                    <h3>No spin campaign found</h3>
                                    <p>Create a monthly campaign to start case-based spin rules.</p>
                                    <a href="{{ route('portal.spin-campaigns.create') }}" class="btn btn-dark">Add Campaign</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            {{ $campaigns->links() }}
        </div>
    </div>
</div>

<style>
    .spin-page {
        max-width: 1500px;
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
    }

    .page-header p {
        margin: 8px 0 0;
        color: #6b7280;
        font-size: 15px;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .summary-card {
        background: #ffffff;
        border: 1px solid #eeeeee;
        border-radius: 18px;
        padding: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
    }

    .summary-card span {
        display: block;
        color: #6b7280;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .summary-card strong {
        display: block;
        font-size: 24px;
        font-weight: 900;
        color: #0d1b2a;
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
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 0 14px;
        background: #f9fafb;
    }

    .search-box input {
        border: 0;
        outline: none;
        background: transparent;
        width: 100%;
        font-size: 14px;
    }

    .campaign-filter select {
        height: 46px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 0 14px;
        background: #ffffff;
        outline: none;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        padding: 12px 16px;
        border: 0;
        cursor: pointer;
        text-decoration: none;
        font-weight: 800;
        font-size: 14px;
        white-space: nowrap;
    }

    .btn-dark {
        background: #0d1b2a;
        color: #ffffff;
    }

    .btn-dark:hover {
        background: #08111d;
    }

    .btn-light {
        background: #f3f4f6;
        color: #111827;
    }

    .btn-danger {
        background: #dc2626;
        color: #ffffff;
    }

    .btn-sm {
        padding: 8px 11px;
        font-size: 12px;
        border-radius: 10px;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .campaign-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .campaign-table thead th {
        background: #f9fafb;
        color: #374151;
        padding: 15px 14px;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 1px solid #e5e7eb;
        text-align: left;
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
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 260px;
    }

    .campaign-icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        background: #eef2ff;
        color: #0d1b2a;
        font-size: 18px;
        flex: 0 0 auto;
    }

    .campaign-name strong,
    .campaign-table td strong {
        display: block;
        font-size: 14px;
        font-weight: 900;
        color: #111827;
    }

    .campaign-name small,
    .campaign-table td small {
        display: block;
        color: #6b7280;
        font-size: 12px;
        margin-top: 4px;
        line-height: 1.45;
    }

    .date-range span {
        display: block;
        font-weight: 800;
        color: #111827;
        font-size: 13px;
    }

    .date-range small {
        color: #6b7280;
        font-size: 11px;
    }

    .progress-info {
        min-width: 190px;
    }

    .progress-top {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        font-size: 12px;
        margin-bottom: 8px;
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
        border-radius: 999px;
        background: #e5e7eb;
        overflow: hidden;
    }

    .progress-bar div {
        height: 100%;
        background: #0d1b2a;
        border-radius: 999px;
    }

    .special-pill {
        display: inline-flex;
        align-items: center;
        padding: 7px 11px;
        border-radius: 999px;
        background: #fff7ed;
        color: #c2410c;
        font-weight: 900;
        font-size: 12px;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 7px 11px;
        border-radius: 999px;
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
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        align-items: center;
    }

    .action-group form {
        margin: 0;
    }

    .text-right {
        text-align: right !important;
    }

    .empty-state {
        text-align: center;
        padding: 50px 20px;
    }

    .empty-state div {
        font-size: 40px;
    }

    .empty-state h3 {
        margin: 12px 0 6px;
        font-size: 20px;
        font-weight: 900;
    }

    .empty-state p {
        color: #6b7280;
        margin: 0 0 16px;
    }

    .pagination-wrap {
        margin-top: 18px;
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