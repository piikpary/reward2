@extends('portal.layouts.app')

@section('content')
<style>
    .reward-page {
        padding: 8px 0 35px;
    }

    .reward-header {
        margin-bottom: 22px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
    }

    .reward-title {
        margin: 0;
        color: #071629;
        font-size: 31px;
        font-weight: 900;
    }

    .reward-description {
        margin: 7px 0 0;
        color: #64748b;
        font-size: 14px;
    }

    .summary-grid {
        margin-bottom: 22px;
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 15px;
    }

    .summary-card {
        padding: 19px;
        border: 1px solid #e5eaf0;
        border-radius: 17px;
        background: #ffffff;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.04);
    }

    .summary-card span {
        display: block;
        margin-bottom: 8px;
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .summary-card strong {
        color: #071629;
        font-size: 24px;
        font-weight: 900;
    }

    .verify-card,
    .filter-card,
    .table-card {
        margin-bottom: 22px;
        border: 1px solid #e5eaf0;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 12px 34px rgba(15, 23, 42, 0.05);
    }

    .verify-card {
        padding: 22px;
        border-color: #ddd6fe;
        background: #faf8ff;
    }

    .verify-card h3 {
        margin: 0 0 6px;
        color: #4c1d95;
        font-size: 18px;
        font-weight: 900;
    }

    .verify-card p {
        margin: 0 0 17px;
        color: #6b7280;
        font-size: 13px;
    }

    .verify-form {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 12px;
    }

    .form-control {
        width: 100%;
        height: 46px;
        padding: 0 14px;
        border: 1px solid #d8e0e8;
        border-radius: 11px;
        background: #ffffff;
        color: #071629;
        font-size: 14px;
        box-sizing: border-box;
        outline: none;
    }

    .form-control:focus {
        border-color: #6d28d9;
        box-shadow: 0 0 0 4px rgba(109, 40, 217, 0.08);
    }

    .verify-btn {
        min-height: 46px;
        padding: 0 20px;
        border: 0;
        border-radius: 11px;
        background: #6d28d9;
        color: #ffffff;
        font-size: 14px;
        font-weight: 850;
        cursor: pointer;
    }

    .filter-card {
        padding: 18px;
    }

    .filter-form {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 220px auto;
        gap: 12px;
    }

    .filter-btn {
        min-height: 46px;
        padding: 0 19px;
        border: 1px solid #0b1b2b;
        border-radius: 11px;
        background: #0b1b2b;
        color: #ffffff;
        font-size: 14px;
        font-weight: 850;
        cursor: pointer;
    }

    .alert {
        margin-bottom: 18px;
        padding: 14px 17px;
        border-radius: 11px;
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
    }

    .table-header {
        padding: 20px 22px;
        border-bottom: 1px solid #edf1f5;
    }

    .table-header h3 {
        margin: 0;
        color: #071629;
        font-size: 19px;
        font-weight: 900;
    }

    .table-header p {
        margin: 5px 0 0;
        color: #64748b;
        font-size: 13px;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .reward-table {
        width: 100%;
        min-width: 1250px;
        border-collapse: collapse;
    }

    .reward-table th {
        padding: 14px 16px;
        border-bottom: 1px solid #e9edf2;
        background: #f8fafc;
        color: #475569;
        font-size: 11px;
        font-weight: 900;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .reward-table td {
        padding: 16px;
        border-bottom: 1px solid #edf1f5;
        color: #172033;
        font-size: 13px;
        vertical-align: top;
    }

    .reward-code {
        display: inline-flex;
        padding: 7px 10px;
        border: 1px dashed #8b5cf6;
        border-radius: 8px;
        background: #f5f3ff;
        color: #5b21b6;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0.5px;
    }

    .discount-badge,
    .status-badge {
        display: inline-flex;
        min-height: 28px;
        padding: 0 10px;
        border-radius: 999px;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 850;
        white-space: nowrap;
    }

    .discount-badge {
        background: #f3e8ff;
        color: #7e22ce;
    }

    .status-waiting {
        background: #e0f2fe;
        color: #0369a1;
    }

    .status-winner {
        background: #fef3c7;
        color: #92400e;
    }

    .status-verified {
        background: #dcfce7;
        color: #15803d;
    }

    .winner-info strong,
    .winner-info span {
        display: block;
    }

    .winner-info strong {
        color: #071629;
        font-weight: 850;
    }

    .winner-info span {
        margin-top: 4px;
        color: #64748b;
        font-size: 12px;
    }

    .pagination-wrap {
        padding: 18px 22px;
    }

    .empty-cell {
        padding: 50px 20px !important;
        color: #64748b !important;
        text-align: center;
    }

    @media (max-width: 950px) {
        .summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .filter-form {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 650px) {
        .reward-header {
            flex-direction: column;
        }

        .summary-grid {
            grid-template-columns: 1fr;
        }

        .verify-form {
            grid-template-columns: 1fr;
        }

        .verify-btn,
        .filter-btn {
            width: 100%;
        }
    }
</style>

<div class="reward-page">
    <div class="reward-header">
        <div>
            <h1 class="reward-title">
                Special Spin Rewards
            </h1>

            <p class="reward-description">
                View generated codes, special-spin winners, and verification status.
            </p>
        </div>
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
            <span>Total Rewards</span>
            <strong>{{ number_format($summary['total']) }}</strong>
        </div>

        <div class="summary-card">
            <span>Waiting for Winner</span>
            <strong>{{ number_format($summary['waiting']) }}</strong>
        </div>

        <div class="summary-card">
            <span>Waiting Verification</span>
            <strong>{{ number_format($summary['winner']) }}</strong>
        </div>

        <div class="summary-card">
            <span>Verified</span>
            <strong>{{ number_format($summary['verified']) }}</strong>
        </div>
    </div>

    <div class="verify-card">
        <h3>Verify Winner Code</h3>

        <p>
            Enter the code shown by the winning customer.
        </p>

        <form
            method="POST"
            action="{{ route('portal.special-spin-rewards.verify') }}"
            class="verify-form"
        >
            @csrf

            <input
                type="text"
                name="reward_code"
                class="form-control"
                value="{{ old('reward_code') }}"
                placeholder="Example: SSR-K7P2MX9Q"
                required
            >

            <button
                type="submit"
                class="verify-btn"
            >
                Verify Code
            </button>
        </form>
    </div>

    <div class="filter-card">
        <form
            method="GET"
            action="{{ route('portal.special-spin-rewards.index') }}"
            class="filter-form"
        >
            <input
                type="text"
                name="search"
                class="form-control"
                value="{{ request('search') }}"
                placeholder="Search code, customer, or phone"
            >

            <select
                name="status"
                class="form-control"
            >
                <option value="">
                    All statuses
                </option>

                <option
                    value="waiting"
                    @selected(request('status') === 'waiting')
                >
                    Waiting for winner
                </option>

                <option
                    value="winner"
                    @selected(request('status') === 'winner')
                >
                    Waiting verification
                </option>

                <option
                    value="verified"
                    @selected(request('status') === 'verified')
                >
                    Verified
                </option>
            </select>

            <button
                type="submit"
                class="filter-btn"
            >
                Search
            </button>
        </form>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3>Special Reward Records</h3>

            <p>
                Reward codes are generated immediately when the special spin is configured.
            </p>
        </div>

        <div class="table-responsive">
            <table class="reward-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Campaign</th>
                        <th>Subcampaign</th>
                        <th>Scope</th>
                        <th>Discount</th>
                        <th>Winner</th>
                        <th>Won At</th>
                        <th>Status</th>
                        <th>Verified At</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($specialRewards as $reward)
                        @php
                            if ($reward->is_redeemed) {
                                $statusText = 'Verified';
                                $statusClass = 'status-verified';
                            } elseif ($reward->is_used) {
                                $statusText = 'Waiting verification';
                                $statusClass = 'status-winner';
                            } else {
                                $statusText = 'Waiting for winner';
                                $statusClass = 'status-waiting';
                            }
                        @endphp

                        <tr>
                            <td>
                                <span class="reward-code">
                                    {{ $reward->reward_code ?? '-' }}
                                </span>
                            </td>

                            <td>
                                {{ $reward->campaign?->name ?? '-' }}
                            </td>

                            <td>
                                {{
                                    $reward->assignedSubCampaign?->name
                                        ?? '-'
                                }}
                            </td>

                            <td>
                                {{
                                    $reward->scope_type === 'main_campaign'
                                        ? 'Main Campaign'
                                        : 'Subcampaign'
                                }}
                            </td>

                            <td>
                                <span class="discount-badge">
                                    {{
                                        number_format(
                                            (float) $reward->special_discount,
                                            2
                                        )
                                    }}%
                                </span>
                            </td>

                            <td>
                                @if($reward->is_used)
                                    <div class="winner-info">
                                        <strong>
                                            {{
                                                $reward->winner?->name
                                                    ?? 'User #' . $reward->used_by_user_id
                                            }}
                                        </strong>

                                        <span>
                                            {{
                                                $reward->winner?->phone_number
                                                    ?? '-'
                                            }}
                                        </span>
                                    </div>
                                @else
                                    -
                                @endif
                            </td>

                            <td>
                                {{
                                    $reward->redeemed_at
                                        ? $reward->redeemed_at->format('d M Y H:i')
                                        : '-'
                                }}
                            </td>

                            <td>
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $statusText }}
                                </span>
                            </td>

                            <td>
                                {{
                                    $reward->redeemed_at
                                        ? $reward->redeemed_at->format(
                                            'd M Y H:i'
                                        )
                                        : '-'
                                }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="9"
                                class="empty-cell"
                            >
                                No special spin reward records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($specialRewards->hasPages())
            <div class="pagination-wrap">
                {{ $specialRewards->links() }}
            </div>
        @endif
    </div>
</div>
@endsection