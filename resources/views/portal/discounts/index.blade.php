@extends('portal.layouts.app')

@section('content')
    <div class="dashboard-header">
        <div>
            <h1 class="page-title">Discount Management</h1>
            <p class="page-subtitle">Manage global discount percentages for the mobile app.</p>
        </div>

        <a href="{{ route('portal.discounts.create') }}" class="quick-btn">
            Add Discount
        </a>
    </div>

    @if (session('success'))
        <div class="success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('portal.discounts.index') }}" class="filter-form">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    value="{{ $search }}"
                    placeholder="Search discount percentage"
                >

                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>

                <button type="submit" class="save-btn">Search</button>
            </form>

            <div class="table-wrap">
                <table class="portal-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Discount</th>
                            <th>Status</th>
                            <th>Created Date</th>
                            <th>Updated Date</th>
                            <th width="220">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($discounts as $discount)
                            <tr>
                                <td>{{ $discount->id }}</td>
                                <td>
                                    <strong>{{ $discount->discount_percentage }}%</strong>
                                </td>
                                <td>
                                    <span class="badge {{ $discount->status === 'active' ? 'badge-active' : 'badge-inactive' }}">
                                        {{ ucfirst($discount->status) }}
                                    </span>
                                </td>
                                <td>{{ $discount->created_at?->format('Y-m-d H:i:s') }}</td>
                                <td>{{ $discount->updated_at?->format('Y-m-d H:i:s') }}</td>
                                <td>
                                    <div class="action-group">
                                        <a href="{{ route('portal.discounts.edit', $discount) }}" class="btn-small edit">
                                            Edit
                                        </a>

                                        <form method="POST" action="{{ route('portal.discounts.toggle-status', $discount) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn-small status">
                                                {{ $discount->status === 'active' ? 'Inactive' : 'Active' }}
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('portal.discounts.destroy', $discount) }}"
                                              onsubmit="return confirm('Delete this discount?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-small delete">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty">No discounts found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 18px;">
                {{ $discounts->links() }}
            </div>
        </div>
    </div>

    <style>
        .filter-form {
            display: grid;
            grid-template-columns: 1fr 220px auto;
            gap: 10px;
            margin-bottom: 18px;
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
            white-space: nowrap;
        }

        .portal-table td {
            padding: 14px;
            border-bottom: 1px solid #eeeeee;
            color: #000;
            vertical-align: middle;
            font-size: 14px;
            white-space: nowrap;
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
            background: #f3f4f6;
            color: #6b7280;
        }

        .action-group {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .btn-small {
            border: none;
            text-decoration: none;
            padding: 8px 10px;
            border-radius: 9px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-small.edit {
            background: #0d1b2a;
            color: #fff;
        }

        .btn-small.status {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .btn-small.delete {
            background: #fee2e2;
            color: #b91c1c;
        }

        .empty {
            text-align: center;
            color: #777;
            padding: 30px !important;
        }

        @media (max-width: 768px) {
            .filter-form {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection