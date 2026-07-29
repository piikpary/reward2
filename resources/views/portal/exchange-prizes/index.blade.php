@extends('portal.layouts.app')

@push('styles')
    <style>
        .ep-page {
            width: 100%;
        }

        .ep-page-header {
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
        }

        .ep-page-title {
            margin: 0;
            color: #0f172a;
            font-size: 30px;
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -0.04em;
        }

        .ep-page-subtitle {
            max-width: 700px;
            margin: 8px 0 0;
            color: #64748b;
            font-size: 14px;
            line-height: 1.7;
        }

        .ep-add-button {
            min-height: 46px;
            padding: 0 20px;
            border: none;
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
            box-shadow:
                0 8px 20px
                rgba(13, 27, 42, 0.17);
            transition: 0.2s ease;
        }

        .ep-add-button:hover {
            background: #08111d;
            color: #ffffff;
            transform: translateY(-1px);
        }

        .ep-alert-success,
        .ep-alert-error {
            margin-bottom: 20px;
            padding: 14px 16px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
        }

        .ep-alert-success {
            border: 1px solid #a7f3d0;
            background: #ecfdf5;
            color: #047857;
        }

        .ep-alert-error {
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #b91c1c;
        }

        .ep-summary-grid {
            margin-bottom: 20px;
            display: grid;
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .ep-summary-card {
            padding: 18px 20px;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            background:
                linear-gradient(
                    135deg,
                    #f8fafc 0%,
                    #ffffff 100%
                );
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .ep-summary-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: #0d1b2a;
            color: #ffffff;
            display: grid;
            place-items: center;
            flex: 0 0 48px;
            font-size: 21px;
        }

        .ep-summary-label {
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .ep-summary-value {
            margin-top: 4px;
            color: #0f172a;
            font-size: 25px;
            font-weight: 800;
            line-height: 1;
        }

        .ep-card {
            overflow: hidden;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            background: #ffffff;
            box-shadow:
                0 10px 30px
                rgba(15, 23, 42, 0.06);
        }

        .ep-card-header {
            padding: 20px 22px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .ep-card-title {
            margin: 0;
            color: #111827;
            font-size: 17px;
            font-weight: 800;
        }

        .ep-card-subtitle {
            margin: 5px 0 0;
            color: #64748b;
            font-size: 13px;
        }

        .ep-count-badge {
            padding: 7px 11px;
            border-radius: 999px;
            background: #f1f5f9;
            color: #334155;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .ep-table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .ep-table {
            width: 100%;
            min-width: 1100px;
            border-collapse: collapse;
        }

        .ep-table th {
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

        .ep-table td {
            padding: 16px 18px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-size: 14px;
            vertical-align: middle;
        }

        .ep-table tbody tr {
            transition: 0.18s ease;
        }

        .ep-table tbody tr:hover {
            background: #fafcff;
        }

        .ep-table tbody tr:last-child td {
            border-bottom: none;
        }

        .ep-row-number {
            width: 45px;
            color: #94a3b8;
            font-weight: 700;
        }

        .ep-product-cell {
            min-width: 300px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .ep-product-image {
            width: 72px;
            height: 72px;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: #f8fafc;
            object-fit: cover;
            flex: 0 0 72px;
        }

        .ep-product-placeholder {
            width: 72px;
            height: 72px;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: #f1f5f9;
            color: #64748b;
            display: grid;
            place-items: center;
            flex: 0 0 72px;
            font-size: 24px;
        }

        .ep-product-title {
            color: #0f172a;
            font-size: 14px;
            font-weight: 800;
            line-height: 1.5;
        }

        .ep-product-id {
            margin-top: 4px;
            color: #94a3b8;
            font-size: 11px;
        }

        .ep-category-badge {
            padding: 7px 11px;
            border-radius: 999px;
            background: #eef2ff;
            color: #3730a3;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .ep-category-empty {
            background: #f1f5f9;
            color: #64748b;
        }

        .ep-discount-value {
            color: #047857;
            font-size: 16px;
            font-weight: 800;
            white-space: nowrap;
        }

        .ep-date-main {
            color: #334155;
            font-weight: 700;
            white-space: nowrap;
        }

        .ep-date-sub {
            margin-top: 3px;
            color: #94a3b8;
            font-size: 11px;
        }

        .ep-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .ep-edit-button,
        .ep-delete-button {
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

        .ep-edit-button {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
        }

        .ep-edit-button:hover {
            border-color: #0d1b2a;
            background: #f8fafc;
            color: #0d1b2a;
        }

        .ep-delete-button {
            border: 1px solid #fecaca;
            background: #fff7f7;
            color: #dc2626;
        }

        .ep-delete-button:hover {
            border-color: #dc2626;
            background: #fef2f2;
        }

        .ep-empty {
            padding: 60px 20px !important;
            text-align: center;
        }

        .ep-empty-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            border-radius: 18px;
            background: #f1f5f9;
            display: grid;
            place-items: center;
            font-size: 28px;
        }

        .ep-empty-title {
            color: #0f172a;
            font-size: 17px;
            font-weight: 800;
        }

        .ep-empty-text {
            margin: 7px 0 18px;
            color: #64748b;
            font-size: 13px;
        }

        .ep-pagination {
            padding: 18px 22px;
            border-top: 1px solid #e5e7eb;
            background: #fafafa;
        }

        @media (max-width: 900px) {
            .ep-page-header {
                flex-direction: column;
            }

            .ep-add-button {
                width: 100%;
            }

            .ep-summary-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            .ep-card-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .ep-page-title {
                font-size: 26px;
            }
        }
    </style>
@endpush

@section('title', 'Exchange Products')

@section('content')
    <div class="ep-page">
        <div class="ep-page-header">
            <div>
                <h1 class="ep-page-title">
                    Exchange Products
                </h1>

                <p class="ep-page-subtitle">
                    Manage products that users can exchange
                    using their available Discount balance.
                </p>
            </div>

            <a
                href="{{ route(
                    'portal.exchange-prizes.create'
                ) }}"
                class="ep-add-button"
            >
                <span>＋</span>
                <span>Add Product</span>
            </a>
        </div>

        @if (session('success'))
            <div class="ep-alert-success">
                ✓ {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="ep-alert-error">
                {{ session('error') }}
            </div>
        @endif

        <div class="ep-summary-grid">
            <div class="ep-summary-card">
                <div class="ep-summary-icon">
                    🎁
                </div>

                <div>
                    <div class="ep-summary-label">
                        Total Exchange Products
                    </div>

                    <div class="ep-summary-value">
                        {{
                            number_format(
                                $exchangePrizes->total()
                            )
                        }}
                    </div>
                </div>
            </div>

            <div class="ep-summary-card">
                <div class="ep-summary-icon">
                    📦
                </div>

                <div>
                    <div class="ep-summary-label">
                        Products On This Page
                    </div>

                    <div class="ep-summary-value">
                        {{
                            number_format(
                                $exchangePrizes->count()
                            )
                        }}
                    </div>
                </div>
            </div>
        </div>

        <div class="ep-card">
            <div class="ep-card-header">
                <div>
                    <h2 class="ep-card-title">
                        Product List
                    </h2>

                    <p class="ep-card-subtitle">
                        View, edit, or delete exchange products.
                    </p>
                </div>

                <div class="ep-count-badge">
                    {{
                        number_format(
                            $exchangePrizes->total()
                        )
                    }}
                    {{
                        $exchangePrizes->total() === 1
                            ? 'product'
                            : 'products'
                    }}
                </div>
            </div>

            <div class="ep-table-wrapper">
                <table class="ep-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Exchange Discount</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse (
                            $exchangePrizes as $exchangePrize
                        )
                            <tr>
                                <td class="ep-row-number">
                                    {{
                                        ($exchangePrizes
                                            ->currentPage() - 1)
                                        * $exchangePrizes
                                            ->perPage()
                                        + $loop->iteration
                                    }}
                                </td>

                                <td>
                                    <div class="ep-product-cell">
                                        @if (
                                            $exchangePrize
                                                ->imageUrl()
                                        )
                                            <img
                                                src="{{
                                                    $exchangePrize
                                                        ->imageUrl()
                                                }}"
                                                alt="{{
                                                    $exchangePrize
                                                        ->title
                                                }}"
                                                class="ep-product-image"
                                            >
                                        @else
                                            <div
                                                class="
                                                    ep-product-placeholder
                                                "
                                            >
                                                🎁
                                            </div>
                                        @endif

                                        <div>
                                            <div
                                                class="
                                                    ep-product-title
                                                "
                                            >
                                                {{
                                                    $exchangePrize
                                                        ->title
                                                }}
                                            </div>

                                            <div
                                                class="
                                                    ep-product-id
                                                "
                                            >
                                                Product ID:
                                                {{
                                                    $exchangePrize
                                                        ->id
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span
                                        class="
                                            ep-category-badge
                                            {{
                                                $exchangePrize
                                                    ->category
                                                    ? ''
                                                    : 'ep-category-empty'
                                            }}
                                        "
                                    >
                                        <span>📦</span>

                                        <span>
                                            {{
                                                $exchangePrize
                                                    ->category
                                                    ?->name
                                                ?? 'Uncategorized'
                                            }}
                                        </span>
                                    </span>
                                </td>

                                <td>
                                    <span
                                        class="
                                            ep-discount-value
                                        "
                                    >
                                        {{
                                            number_format(
                                                (float)
                                                $exchangePrize
                                                    ->exchange_discount_amount,
                                                2
                                            )
                                        }}
                                    </span>
                                </td>

                                <td>
                                    <div class="ep-date-main">
                                        {{
                                            $exchangePrize
                                                ->created_at
                                                ?->format(
                                                    'd M Y'
                                                )
                                            ?? '-'
                                        }}
                                    </div>

                                    <div class="ep-date-sub">
                                        {{
                                            $exchangePrize
                                                ->created_at
                                                ?->format(
                                                    'H:i'
                                                )
                                            ?? ''
                                        }}
                                    </div>
                                </td>

                                <td>
                                    <div class="ep-actions">
                                        <a
                                            href="{{ route(
                                                'portal.exchange-prizes.edit',
                                                $exchangePrize
                                            ) }}"
                                            class="ep-edit-button"
                                        >
                                            ✎ Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'portal.exchange-prizes.destroy',
                                                $exchangePrize
                                            ) }}"
                                            style="margin: 0;"
                                            onsubmit="
                                                return confirm(
                                                    'Are you sure you want to delete this exchange product?'
                                                );
                                            "
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="
                                                    ep-delete-button
                                                "
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
                                    class="ep-empty"
                                >
                                    <div class="ep-empty-icon">
                                        🎁
                                    </div>

                                    <div class="ep-empty-title">
                                        No exchange products found
                                    </div>

                                    <div class="ep-empty-text">
                                        Create your first product
                                        for users to exchange.
                                    </div>

                                    <a
                                        href="{{ route(
                                            'portal.exchange-prizes.create'
                                        ) }}"
                                        class="ep-add-button"
                                    >
                                        ＋ Add First Product
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($exchangePrizes->hasPages())
                <div class="ep-pagination">
                    {{ $exchangePrizes->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection