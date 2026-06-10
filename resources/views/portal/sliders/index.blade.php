@extends('portal.layouts.app')

@section('content')
    <div class="dashboard-header">
        <div>
            <h1 class="page-title">Sliders</h1>
            <p class="page-subtitle">Manage homepage banners for the mobile app.</p>
        </div>

        <a href="{{ route('portal.sliders.create') }}" class="quick-btn primary">
            Add Slider
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            @if (session('success'))
                <div class="success">{{ session('success') }}</div>
            @endif

            <div class="table-wrap">
                <table class="portal-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Link</th>
                            <th>Sort</th>
                            <th>Status</th>
                            <th width="160">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($sliders as $slider)
                            <tr>
                                <td>
                                    @if ($slider->image)
                                        <img src="{{ asset('storage/' . $slider->image) }}" class="slider-thumb">
                                    @else
                                        <span class="muted">No image</span>
                                    @endif
                                </td>
                                <td>{{ $slider->title ?? '-' }}</td>
                                <td>{{ $slider->link ?? '-' }}</td>
                                <td>{{ $slider->sort_order }}</td>
                                <td>
                                    <span class="badge {{ $slider->status ? 'badge-active' : 'badge-inactive' }}">
                                        {{ $slider->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-row">
                                        <a href="{{ route('portal.sliders.edit', $slider) }}" class="btn-small edit">
                                            Edit
                                        </a>

                                        <form method="POST" action="{{ route('portal.sliders.destroy', $slider) }}"
                                              onsubmit="return confirm('Delete this slider?')">
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
                                <td colspan="6" class="empty">No sliders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 18px;">
                {{ $sliders->links() }}
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

        .slider-thumb {
            width: 120px;
            height: 64px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid #eeeeee;
        }

        .muted {
            color: #777;
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