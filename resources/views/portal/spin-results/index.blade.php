@extends('portal.layouts.app')

@push('styles')
<style>
    .spin-results-page {
        max-width: 100%;
    }

    .spin-results-header {
        margin-bottom: 24px;
    }

    .spin-results-title {
        margin: 0;
        font-size: 30px;
        font-weight: 800;
        letter-spacing: -0.04em;
        color: #000000;
    }

    .spin-results-subtitle {
        margin: 8px 0 0;
        color: #6b7280;
        font-size: 14px;
    }

    .filter-card {
        margin-bottom: 22px;
        padding: 22px;
        border: 1px solid #eeeeee;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 16px;
        align-items: end;
    }

    .filter-group label {
        display: block;
        margin-bottom: 7px;
        font-size: 13px;
        font-weight: 700;
        color: #111827;
    }

    .filter-control {
        width: 100%;
        height: 46px;
        padding: 0 14px;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        background: #ffffff;
        color: #111827;
        font-size: 14px;
        outline: none;
    }

    .filter-control:focus {
        border-color: #0d1b2a;
        box-shadow: 0 0 0 4px rgba(13, 27, 42, 0.12);
    }

    .filter-actions {
        margin-top: 16px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-filter {
        height: 44px;
        padding: 0 20px;
        border: none;
        border-radius: 12px;
        background: #0d1b2a;
        color: #ffffff;
        cursor: pointer;
        font-size: 14px;
        font-weight: 800;
    }

    .btn-filter:hover {
        background: #08111d;
    }

    .btn-reset {
        height: 44px;
        padding: 0 20px;
        border-radius: 12px;
        background: #f3f4f6;
        color: #111827;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        font-size: 14px;
        font-weight: 800;
    }

    .btn-reset:hover {
        background: #e5e7eb;
        text-decoration: none;
    }

    .results-card {
        overflow: hidden;
        border: 1px solid #eeeeee;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }

    .table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .results-table {
        width: 100%;
        border-collapse: collapse;
        white-space: nowrap;
    }

    .results-table thead {
        background: #f7f8fa;
    }

    .results-table th {
        padding: 15px 16px;
        border-bottom: 1px solid #eeeeee;
        color: #374151;
        text-align: left;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .results-table td {
        padding: 15px 16px;
        border-bottom: 1px solid #f1f1f1;
        color: #111827;
        font-size: 14px;
        vertical-align: middle;
    }

    .results-table tbody tr:hover {
        background: #fafafa;
    }

    .customer-name {
        font-weight: 800;
        color: #111827;
    }

    .customer-email {
        margin-top: 4px;
        color: #6b7280;
        font-size: 12px;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 70px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
    }

    .badge-discount {
        background: #dcfce7;
        color: #047857;
    }

    .badge-total {
        background: #ede9fe;
        color: #6d28d9;
    }

    .empty-row {
        padding: 40px 16px !important;
        text-align: center;
        color: #6b7280 !important;
    }

    .pagination-wrap {
        padding: 18px;
        border-top: 1px solid #eeeeee;
        background: #ffffff;
    }

    @media (max-width: 1200px) {
        .filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 640px) {
        .filter-grid {
            grid-template-columns: 1fr;
        }

        .spin-results-title {
            font-size: 26px;
        }
    }
</style>
@endpush

@section('content')
<div class="spin-results-page">

    <div class="spin-results-header">
        <h1 class="spin-results-title">
            Spin Results
        </h1>

        <p class="spin-results-subtitle">
            View customer spin history and discount results.
        </p>
    </div>

    <div class="filter-card">
        <form
            method="GET"
            action="{{ route('portal.spin-results.index') }}"
        >
            <div class="filter-grid">

                <div class="filter-group">
                    <label>
                        Campaign
                    </label>

                    <select
                        name="campaign_id"
                        class="filter-control"
                    >
                        <option value="">
                            All Campaigns
                        </option>

                        @foreach ($campaigns as $campaign)
                            <option
                                value="{{ $campaign->id }}"
                                @selected(request('campaign_id') == $campaign->id)
                            >
                                {{ $campaign->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label>
                        User ID
                    </label>

                    <input
                        type="number"
                        name="user_id"
                        value="{{ request('user_id') }}"
                        class="filter-control"
                        placeholder="Example: 1"
                    >
                </div>

                <div class="filter-group">
                    <label>
                        Case Number
                    </label>

                    <input
                        type="number"
                        name="case_number"
                        value="{{ request('case_number') }}"
                        class="filter-control"
                        placeholder="Example: 71"
                    >
                </div>

                <div class="filter-group">
                    <label>
                        From Date
                    </label>

                    <input
                        type="date"
                        name="from_date"
                        value="{{ request('from_date') }}"
                        class="filter-control"
                    >
                </div>

                <div class="filter-group">
                    <label>
                        To Date
                    </label>

                    <input
                        type="date"
                        name="to_date"
                        value="{{ request('to_date') }}"
                        class="filter-control"
                    >
                </div>

            </div>

            <div class="filter-actions">
                <button
                    type="submit"
                    class="btn-filter"
                >
                    Filter
                </button>

                <a
                    href="{{ route('portal.spin-results.index') }}"
                    class="btn-reset"
                >
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="results-card">
        <div class="table-wrap">
            <table class="results-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Campaign</th>
                        <th>Subcampaign</th>
                        <th>Case No.</th>
                        <th>Spin No.</th>
                        <th>Discount</th>
                        <th>Case Total</th>
                        <th>Date</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($results as $result)
                        <tr>
                            <td>
                                {{ $result->id }}
                            </td>

                            <td>
                                <div class="customer-name">
                                    {{ $result->user?->name ?? 'User #' . $result->user_id }}
                                </div>

                                @if ($result->user?->email)
                                    <div class="customer-email">
                                        {{ $result->user->email }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                {{ $result->campaign?->name ?? 'Campaign #' . $result->spin_campaign_id }}
                            </td>

                            <td>
                                {{ $result->subCampaign?->name ?? 'Subcampaign #' . $result->spin_sub_campaign_id }}
                            </td>

                            <td>
                                {{ $result->case_number }}
                            </td>

                            <td>
                                {{ $result->spin_number }}
                            </td>

                            <td>
                                <span class="badge badge-discount">
                                    {{ number_format((float) $result->discount_percentage, 2) }}%
                                </span>
                            </td>

                            <td>
                                <span class="badge badge-total">
                                    {{ number_format((float) $result->case_total_discount, 2) }}%
                                </span>
                            </td>

                            <td>
                                {{ $result->created_at?->format('Y-m-d H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="9"
                                class="empty-row"
                            >
                                No spin results found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            {{ $results->links() }}
        </div>
    </div>

</div>
@endsection