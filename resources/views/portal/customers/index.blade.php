@extends('portal.layouts.app')

@section('content')
    <div class="dashboard-header">
        <div>
            <h1 class="page-title">Customers</h1>
            <p class="page-subtitle">Manage customer wallet and spin quantity.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('portal.customers.index') }}" style="margin-bottom: 18px;">
                <div style="display: flex; gap: 10px;">
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        value="{{ $search }}"
                        placeholder="Search by phone, name or email"
                    >
                    <button type="submit" class="save-btn">Search</button>
                </div>
            </form>

            <div class="table-wrap">
                <table class="portal-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Phone</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th width="140">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($customers as $customer)
                            <tr>
                                <td>{{ $customer->id }}</td>
                                <td>{{ $customer->phone_number ?? '-' }}</td>
                                <td>{{ $customer->name ?? '-' }}</td>
                                <td>{{ $customer->email ?? '-' }}</td>
                                <td>
                                    <span class="badge badge-active">Active</span>
                                </td>
                                <td>
                                    <a href="{{ route('portal.customers.wallet', $customer) }}" class="btn-small edit">
                                        Wallet
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty">No customers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 18px;">
                {{ $customers->links() }}
            </div>
        </div>
    </div>

    <style>
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

        .empty {
            text-align: center;
            color: #777;
            padding: 30px !important;
        }
    </style>
@endsection