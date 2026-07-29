@extends('portal.layouts.app')

@push('styles')
    <style>
        .pc-page {
            width: 100%;
        }

        .pc-header {
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
        }

        .pc-header-content {
            min-width: 0;
        }

        .pc-title {
            margin: 0;
            color: #0f172a;
            font-size: 30px;
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -0.04em;
        }

        .pc-subtitle {
            max-width: 650px;
            margin: 8px 0 0;
            color: #64748b;
            font-size: 14px;
            line-height: 1.7;
        }

        .pc-add-button {
            min-height: 46px;
            padding: 0 20px;
            border-radius: 12px;
            background: #0d1b2a;
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            white-space: nowrap;
            box-shadow: 0 8px 20px rgba(13, 27, 42, 0.16);
            transition: 0.2s ease;
        }

        .pc-add-button:hover {
            background: #08111d;
            color: #ffffff;
            transform: translateY(-1px);
        }

        .pc-alert-success {
            margin-bottom: 20px;
            padding: 14px 16px;
            border: 1px solid #a7f3d0;
            border-radius: 12px;
            background: #ecfdf5;
            color: #047857;
            font-size: 14px;
            font-weight: 600;
        }

        .pc-alert-error {
            margin-bottom: 20px;
            padding: 14px 16px;
            border: 1px solid #fecaca;
            border-radius: 12px;
            background: #fef2f2;
            color: #b91c1c;
            font-size: 14px;
            font-weight: 600;
        }

        .pc-summary {
            margin-bottom: 20px;
            padding: 18px 20px;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            background: linear-gradient(
                135deg,
                #f8fafc 0%,
                #ffffff 100%
            );
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .pc-summary-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: #0d1b2a;
            color: #ffffff;
            display: grid;
            place-items: center;
            font-size: 21px;
            flex: 0 0 48px;
        }

        .pc-summary-label {
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .pc-summary-value {
            margin-top: 3px;
            color: #0f172a;
            font-size: 25px;
            font-weight: 800;
            line-height: 1;
        }

        .pc-card {
            overflow: hidden;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        }

        .pc-card-header {
            padding: 20px 22px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .pc-card-title {
            margin: 0;
            color: #111827;
            font-size: 17px;
            font-weight: 800;
        }

        .pc-card-note {
            margin: 5px 0 0;
            color: #64748b;
            font-size: 13px;
        }

        .pc-count-badge {
            padding: 7px 11px;
            border-radius: 999px;
            background: #f1f5f9;
            color: #334155;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .pc-table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .pc-table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
        }

        .pc-table th {
            padding: 14px 18px;
            border-bottom: 1px solid #e5e7eb;
            background: #f8fafc;
            color: #64748b;
            text-align: left;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
        }

        .pc-table td {
            padding: 17px 18px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-size: 14px;
            vertical-align: middle;
        }

        .pc-table tbody tr {
            transition: 0.18s ease;
        }

        .pc-table tbody tr:hover {
            background: #fafcff;
        }

        .pc-table tbody tr:last-child td {
            border-bottom: none;
        }

        .pc-number {
            width: 45px;
            color: #94a3b8;
            font-weight: 700;
        }

        .pc-category-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .pc-category-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: #eef2ff;
            color: #3730a3;
            display: grid;
            place-items: center;
            font-size: 16px;
            font-weight: 800;
            text-transform: uppercase;
            flex: 0 0 42px;
        }

        .pc-category-name {
            color: #0f172a;
            font-size: 14px;
            font-weight: 800;
        }

        .pc-category-id {
            margin-top: 3px;
            color: #94a3b8;
            font-size: 11px;
        }

        .pc-description {
            max-width: 420px;
            color: #64748b;
            line-height: 1.6;
        }

        .pc-product-count {
            min-width: 40px;
            padding: 6px 10px;
            border-radius: 999px;
            background: #f1f5f9;
            color: #334155;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 800;
        }

        .pc-date-main {
            color: #334155;
            font-weight: 700;
            white-space: nowrap;
        }

        .pc-date-sub {
            margin-top: 3px;
            color: #94a3b8;
            font-size: 11px;
        }

        .pc-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .pc-edit-button,
        .pc-delete-button {
            min-height: 36px;
            padding: 0 13px;
            border-radius: 9px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: 0.18s ease;
        }

        .pc-edit-button {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
        }

        .pc-edit-button:hover {
            border-color: #0d1b2a;
            background: #f8fafc;
            color: #0d1b2a;
        }

        .pc-delete-button {
            border: 1px solid #fecaca;
            background: #fff7f7;
            color: #dc2626;
        }

        .pc-delete-button:hover {
            border-color: #dc2626;
            background: #fef2f2;
        }

        .pc-empty {
            padding: 60px 20px !important;
            text-align: center;
        }

        .pc-empty-icon {
            width: 62px;
            height: 62px;
            margin: 0 auto 16px;
            border-radius: 18px;
            background: #f1f5f9;
            display: grid;
            place-items: center;
            font-size: 28px;
        }

        .pc-empty-title {
            color: #0f172a;
            font-size: 17px;
            font-weight: 800;
        }

        .pc-empty-text {
            margin-top: 6px;
            color: #64748b;
            font-size: 13px;
        }

        .pc-pagination {
            padding: 18px 22px;
            border-top: 1px solid #e5e7eb;
            background: #fafafa;
        }

        @media (max-width: 768px) {
            .pc-header {
                flex-direction: column;
            }

            .pc-add-button {
                width: 100%;
            }

            .pc-card-header {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
@endpush

@section('title', 'Product Categories')

@section('content')
    <div class="pc-page">
        <div class="pc-header">
            <div class="pc-header-content">
                <h1 class="pc-title">
                    Product Categories
                </h1>

                <p class="pc-subtitle">
                    Organize exchange prize products into clear
                    categories such as Beer, Water, and Accessories.
                </p>
            </div>

            <a
                href="{{ route(
                    'portal.product-categories.create'
                ) }}"
                class="pc-add-button"
            >
                <span>＋</span>
                <span>Add Category</span>
            </a>
        </div>

        @if (session('success'))
            <div class="pc-alert-success">
                ✓ {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="pc-alert-error">
                {{ session('error') }}
            </div>
        @endif

        <div class="pc-summary">
            <div class="pc-summary-icon">
                📦
            </div>

            <div>
                <div class="pc-summary-label">
                    Total Categories
                </div>

                <div class="pc-summary-value">
                    {{ number_format($categories->total()) }}
                </div>
            </div>
        </div>

        <div class="pc-card">
            <div class="pc-card-header">
                <div>
                    <h2 class="pc-card-title">
                        Category List
                    </h2>

                    <p class="pc-card-note">
                        View, edit, or remove product categories.
                    </p>
                </div>

                <div class="pc-count-badge">
                    {{ number_format($categories->total()) }}
                    {{
                        $categories->total() === 1
                            ? 'category'
                            : 'categories'
                    }}
                </div>
            </div>

            <div class="pc-table-wrapper">
                <table class="pc-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Products</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($categories as $category)
                            <tr>
                                <td class="pc-number">
                                    {{
                                        ($categories->currentPage() - 1)
                                        * $categories->perPage()
                                        + $loop->iteration
                                    }}
                                </td>

                                <td>
                                    <div class="pc-category-cell">
                                        <div class="pc-category-icon">
                                            {{
                                                \Illuminate\Support\Str
                                                    ::upper(
                                                        \Illuminate\Support\Str
                                                            ::substr(
                                                                $category->name,
                                                                0,
                                                                1
                                                            )
                                                    )
                                            }}
                                        </div>

                                        <div>
                                            <div class="pc-category-name">
                                                {{ $category->name }}
                                            </div>

                                            <div class="pc-category-id">
                                                Category ID:
                                                {{ $category->id }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="pc-description">
                                        {{
                                            $category->description
                                                ? \Illuminate\Support\Str
                                                    ::limit(
                                                        $category->description,
                                                        100
                                                    )
                                                : 'No description provided.'
                                        }}
                                    </div>
                                </td>

                                <td>
                                    <span class="pc-product-count">
                                        {{
                                            number_format(
                                                $category
                                                    ->exchange_prizes_count
                                            )
                                        }}
                                    </span>
                                </td>

                                <td>
                                    <div class="pc-date-main">
                                        {{
                                            $category->created_at
                                                ?->format('d M Y')
                                            ?? '-'
                                        }}
                                    </div>

                                    <div class="pc-date-sub">
                                        {{
                                            $category->created_at
                                                ?->format('H:i')
                                            ?? ''
                                        }}
                                    </div>
                                </td>

                                <td>
                                    <div class="pc-actions">
                                        <a
                                            href="{{ route(
                                                'portal.product-categories.edit',
                                                $category
                                            ) }}"
                                            class="pc-edit-button"
                                        >
                                            ✎ Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'portal.product-categories.destroy',
                                                $category
                                            ) }}"
                                            onsubmit="
                                                return confirm(
                                                    'Are you sure you want to delete this category?'
                                                );
                                            "
                                            style="margin: 0;"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="pc-delete-button"
                                            >
                                                🗑 Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="6"
                                    class="pc-empty"
                                >
                                    <div class="pc-empty-icon">
                                        📦
                                    </div>

                                    <div class="pc-empty-title">
                                        No product categories found
                                    </div>

                                    <div class="pc-empty-text">
                                        Create your first category to
                                        organize exchange prize products.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($categories->hasPages())
                <div class="pc-pagination">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection