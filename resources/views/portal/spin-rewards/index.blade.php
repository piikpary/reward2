@extends('portal.layouts.app')

@section('content')
    <div class="dashboard-header">
        <div>
            <h1 class="page-title">Spin Rewards</h1>
            <p class="page-subtitle">Manage discount percentages and winning chances for spin.</p>
        </div>

        <a href="{{ route('portal.spin-rewards.create') }}" class="quick-btn primary">
            Add Reward
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            @if (session('success'))
                <div class="success">{{ session('success') }}</div>
            @endif

            <div class="info-box">
                <strong>Chance Weight:</strong>
                Higher weight means higher chance to win that discount.
                Example: 5% with weight 30 appears more often than 50% with weight 2.
            </div>

            <div class="table-wrap">
                <table class="portal-table">
                    <thead>
                        <tr>
                            <th>Discount</th>
                            <th>Chance Weight</th>
                            <th>Sort</th>
                            <th>Status</th>
                            <th width="160">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($spinRewards as $reward)
                            <tr>
                                <td>
                                    <strong>{{ $reward->discount_percentage }}%</strong>
                                </td>
                                <td>{{ $reward->chance_weight }}</td>
                                <td>{{ $reward->sort_order }}</td>
                                <td>
                                    <span class="badge {{ $reward->status ? 'badge-active' : 'badge-inactive' }}">
                                        {{ $reward->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-row">
                                        <a href="{{ route('portal.spin-rewards.edit', $reward) }}" class="btn-small edit">
                                            Edit
                                        </a>

                                        <form method="POST" action="{{ route('portal.spin-rewards.destroy', $reward) }}"
                                              onsubmit="return confirm('Delete this spin reward?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-small delete">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty">No spin rewards found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 18px;">
                {{ $spinRewards->links() }}
            </div>
        </div>
    </div>

    <style>
        .info-box {
            background: #f8fafc;
            border: 1px solid #eeeeee;
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 18px;
            color: #000;
            font-size: 14px;
        }

        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        .portal-table {
            width: 100%;
            border-collapse: collapse;
        }

        .portal-table th {
            text-align: left;
            color: #000;
            font-size: 13px;
            font-weight: 700;
            padding: 14px;
            border-bottom: 1px solid #eeeeee;
            background: #fafafa;
        }

        .portal-table td {
            padding: 14px;
            border-bottom: 1px solid #eeeeee;
            color: #000;
            vertical-align: middle;
            font-size: 14px;
        }

        .badge {
            display: inline-flex;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        .badge-active {
            background: #ecfdf5;
            color: #047857;
        }

        .badge-inactive {
            background: #fef2f2;
            color: #dc2626;
        }

        .action-row {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .btn-small {
            border: none;
            text-decoration: none;
            padding: 8px 11px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-small.edit {
            background: #0d1b2a;
            color: #fff;
        }

        .btn-small.delete {
            background: #dc2626;
            color: #fff;
        }

        .empty {
            text-align: center;
            color: #777;
            padding: 30px !important;
        }
    </style>
@endsection