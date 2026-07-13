@extends('portal.layouts.app')

@push('styles')
    @include('portal.share-campaigns._styles')

    <style>
        .report-page {
            width: 100%;
        }

        .report-header {
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
        }

        .report-header-left {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .report-title {
            margin: 0;
            color: #000000;
            font-size: 30px;
            font-weight: 700;
            letter-spacing: -0.04em;
        }

        .report-subtitle {
            margin: 0;
            color: #6b7280;
            font-size: 15px;
            line-height: 1.6;
        }

        .report-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .campaign-summary-card {
            margin-bottom: 22px;
            padding: 28px;
            border: 1px solid #eeeeee;
            border-radius: 22px;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .campaign-summary-grid {
            display: grid;
            grid-template-columns: 220px minmax(0, 1fr);
            gap: 28px;
            align-items: start;
        }

        .campaign-poster-box {
            width: 100%;
        }

        .campaign-poster-box .poster-large {
            width: 100%;
            height: 170px;
            border-radius: 18px;
            object-fit: cover;
            border: 1px solid #e5e7eb;
            background: #f7f8fa;
        }

        .campaign-summary-content {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .campaign-summary-top h2 {
            margin: 0 0 8px;
            color: #111827;
            font-size: 36px;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.04em;
        }

        .campaign-summary-top p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
            line-height: 1.8;
        }

        .campaign-meta-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
        }

        .campaign-meta-item {
            padding: 16px;
            border: 1px solid #eeeeee;
            border-radius: 16px;
            background: #fafafa;
        }

        .campaign-meta-label {
            margin-bottom: 7px;
            color: #6b7280;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .campaign-meta-value {
            color: #111827;
            font-size: 20px;
            font-weight: 800;
            line-height: 1.2;
        }

        .campaign-badge-row {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .statistics.statistics-report {
            margin-bottom: 22px;
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 14px;
        }

        .statistics-report .stat {
            padding: 20px;
            border: 1px solid #eeeeee;
            border-radius: 18px;
            background: #ffffff;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
        }

        .statistics-report .stat-label {
            color: #6b7280;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .statistics-report .stat-value {
            margin-top: 10px;
            color: #0f172a;
            font-size: 42px;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.04em;
        }

        .statistics-report .stat-note {
            margin-top: 8px;
            color: #94a3b8;
            font-size: 12px;
            line-height: 1.5;
        }

        .report-table-card {
            padding: 0;
            overflow: hidden;
        }

        .report-table-header {
            padding: 22px 24px 16px;
            border-bottom: 1px solid #eeeeee;
            background: #ffffff;
        }

        .report-table-title {
            margin: 0;
            color: #111827;
            font-size: 18px;
            font-weight: 800;
        }

        .report-table-subtitle {
            margin: 6px 0 0;
            color: #6b7280;
            font-size: 13px;
            line-height: 1.6;
        }

        .report-table-card .table-wrapper {
            padding: 0 18px 18px;
        }

        .campaign-table.report-table {
            min-width: 1200px;
        }

        .campaign-table.report-table th {
            background: #f8fafc;
        }

        .campaign-table.report-table td {
            padding-top: 16px;
            padding-bottom: 16px;
        }

        .table-cell-main {
            color: #111827;
            font-weight: 700;
        }

        .table-cell-sub {
            margin-top: 4px;
            color: #6b7280;
            font-size: 12px;
            line-height: 1.5;
        }

        .facebook-link {
            color: #2563eb;
            font-weight: 700;
            text-decoration: none;
        }

        .facebook-link:hover {
            text-decoration: underline;
        }

        .empty-report {
            padding: 48px 20px !important;
            color: #6b7280;
            text-align: center !important;
        }

        .empty-report strong {
            display: block;
            margin-bottom: 8px;
            color: #111827;
            font-size: 16px;
        }

        @media (max-width: 1200px) {
            .campaign-meta-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .statistics.statistics-report {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 900px) {
            .campaign-summary-grid {
                grid-template-columns: 1fr;
            }

            .campaign-poster-box {
                max-width: 320px;
            }

            .report-header {
                flex-direction: column;
            }
        }

        @media (max-width: 700px) {
            .campaign-meta-grid,
            .statistics.statistics-report {
                grid-template-columns: 1fr;
            }

            .report-actions {
                width: 100%;
            }

            .report-actions .button {
                width: 100%;
            }

            .campaign-summary-top h2 {
                font-size: 28px;
            }

            .statistics-report .stat-value {
                font-size: 34px;
            }
        }
    </style>
@endpush

@section('title', 'Campaign Share Report')

@section('content')
    <div class="report-page">
        <div class="report-header">
            <div class="report-header-left">
                <h1 class="report-title">
                    Customer Share Report
                </h1>

                <p class="report-subtitle">
                    Review customer campaign sharing activity, verification status, and awarded rewards.
                </p>
            </div>

            <div class="report-actions">
                <a
                    class="button button-secondary"
                    href="{{ route('portal.share-campaigns.index') }}"
                >
                    Campaign List
                </a>

                <a
                    class="button button-primary"
                    href="{{ route('portal.share-campaigns.edit', $shareCampaign) }}"
                >
                    Edit Campaign
                </a>
            </div>
        </div>

        <div class="campaign-summary-card">
            <div class="campaign-summary-grid">
                <div class="campaign-poster-box">
                    @if ($shareCampaign->imageUrl())
                        <img
                            class="poster-large"
                            src="{{ $shareCampaign->imageUrl() }}"
                            alt="{{ $shareCampaign->title }}"
                        >
                    @else
                        <div class="poster-large"></div>
                    @endif
                </div>

                <div class="campaign-summary-content">
                    <div class="campaign-summary-top">
                        <h2>
                            {{ $shareCampaign->title }}
                        </h2>

                        <p>
                            {{ $shareCampaign->description ?: 'No campaign description provided.' }}
                        </p>
                    </div>

                    <div class="campaign-meta-grid">
                        <div class="campaign-meta-item">
                            <div class="campaign-meta-label">
                                Required shares
                            </div>

                            <div class="campaign-meta-value">
                                {{ number_format($shareCampaign->required_shares) }}
                            </div>
                        </div>

                        <div class="campaign-meta-item">
                            <div class="campaign-meta-label">
                                Reward spins
                            </div>

                            <div class="campaign-meta-value">
                                {{ number_format($shareCampaign->reward_spins) }}
                            </div>
                        </div>

                        <div class="campaign-meta-item">
                            <div class="campaign-meta-label">
                                Start date
                            </div>

                            <div class="campaign-meta-value" style="font-size: 16px;">
                                {{ $shareCampaign->starts_at?->format('d M Y H:i') ?? 'Immediately' }}
                            </div>
                        </div>

                        <div class="campaign-meta-item">
                            <div class="campaign-meta-label">
                                Expiry date
                            </div>

                            <div class="campaign-meta-value" style="font-size: 16px;">
                                {{ $shareCampaign->expires_at?->format('d M Y H:i') ?? 'No expiry' }}
                            </div>
                        </div>
                    </div>

                    <div class="campaign-badge-row">
                        @if ($shareCampaign->is_active)
                            <span class="badge badge-success">
                                Active
                            </span>
                        @else
                            <span class="badge badge-danger">
                                Inactive
                            </span>
                        @endif

                        @if ($shareCampaign->is_published)
                            <span class="badge badge-success">
                                Published
                            </span>
                        @else
                            <span class="badge badge-gray">
                                Draft
                            </span>
                        @endif

                        @if ($shareCampaign->reward_repeatable)
                            <span class="badge badge-warning">
                                Repeatable reward
                            </span>
                        @endif

                        @if ($shareCampaign->expires_at && $shareCampaign->expires_at->isPast())
                            <span class="badge badge-danger">
                                Expired
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="statistics statistics-report">
            <div class="stat">
                <div class="stat-label">
                    Total shares
                </div>

                <div class="stat-value">
                    {{ number_format($statistics['total_shares']) }}
                </div>

                <div class="stat-note">
                    All customer share submissions for this campaign.
                </div>
            </div>

            <div class="stat">
                <div class="stat-label">
                    Verified shares
                </div>

                <div class="stat-value">
                    {{ number_format($statistics['verified_shares']) }}
                </div>

                <div class="stat-note">
                    Shares successfully verified by the system.
                </div>
            </div>

            <div class="stat">
                <div class="stat-label">
                    Unique customers
                </div>

                <div class="stat-value">
                    {{ number_format($statistics['unique_customers']) }}
                </div>

                <div class="stat-note">
                    Number of customers participating in this campaign.
                </div>
            </div>

            <div class="stat">
                <div class="stat-label">
                    Rewards awarded
                </div>

                <div class="stat-value">
                    {{ number_format($statistics['rewards_awarded']) }}
                </div>

                <div class="stat-note">
                    Total reward-award records created from this campaign.
                </div>
            </div>

            <div class="stat">
                <div class="stat-label">
                    Spins awarded
                </div>

                <div class="stat-value">
                    {{ number_format($statistics['spins_awarded']) }}
                </div>

                <div class="stat-note">
                    Total spin quantity distributed to customers.
                </div>
            </div>
        </div>

        <div class="campaign-card report-table-card">
            <div class="report-table-header">
                <h2 class="report-table-title">
                    Shared Customer List
                </h2>

                <p class="report-table-subtitle">
                    Detailed record of Facebook post submissions, customer information, verification result, and IP address.
                </p>
            </div>

            <div class="table-wrapper">
                <table class="campaign-table report-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>Facebook post</th>
                            <th>Status</th>
                            <th>Verification</th>
                            <th>Shared at</th>
                            <th>IP address</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($shares as $share)
                            @php
                                $customerName =
                                    $share->user?->name
                                    ?? $share->user?->username
                                    ?? $share->user?->phone
                                    ?? 'Customer #' . $share->user_id;

                                $customerContact =
                                    $share->user?->phone
                                    ?? $share->user?->email
                                    ?? 'N/A';
                            @endphp

                            <tr>
                                <td>
                                    <div class="table-cell-main">
                                        {{
                                            ($shares->currentPage() - 1) * $shares->perPage()
                                            + $loop->iteration
                                        }}
                                    </div>
                                </td>

                                <td>
                                    <div class="table-cell-main">
                                        {{ $customerName }}
                                    </div>

                                    <div class="table-cell-sub">
                                        User ID: {{ $share->user_id }}
                                    </div>
                                </td>

                                <td>
                                    <div class="table-cell-main">
                                        {{ $customerContact }}
                                    </div>
                                </td>

                                <td>
                                    <a
                                        class="facebook-link"
                                        href="{{ $share->facebook_post_url }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        Open Facebook post
                                    </a>

                                    <div class="table-cell-sub">
                                        {{ \Illuminate\Support\Str::limit($share->facebook_post_url, 60) }}
                                    </div>
                                </td>

                                <td>
                                    @if ($share->status === 'verified')
                                        <span class="badge badge-success">
                                            Verified
                                        </span>
                                    @else
                                        <span class="badge badge-warning">
                                            {{ ucfirst($share->status) }}
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="table-cell-main">
                                        {{
                                            str_replace(
                                                '_',
                                                ' ',
                                                ucfirst($share->verification_method)
                                            )
                                        }}
                                    </div>
                                </td>

                                <td>
                                    <div class="table-cell-main">
                                        {{ $share->shared_at?->format('d M Y H:i:s') }}
                                    </div>
                                </td>

                                <td>
                                    <div class="table-cell-main">
                                        {{ $share->ip_address ?? 'N/A' }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="empty-report">
                                    <strong>No customer shares yet</strong>
                                    Customer Facebook share submissions will appear here after the API verification request is completed.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($shares->hasPages())
                <div class="pagination-area" style="padding: 0 24px 24px;">
                    <div class="muted">
                        Showing
                        {{ $shares->firstItem() }}
                        to
                        {{ $shares->lastItem() }}
                        of
                        {{ $shares->total() }}
                        records
                    </div>

                    <div class="actions">
                        @if ($shares->previousPageUrl())
                            <a
                                class="button button-secondary"
                                href="{{ $shares->previousPageUrl() }}"
                            >
                                Previous
                            </a>
                        @endif

                        @if ($shares->nextPageUrl())
                            <a
                                class="button button-secondary"
                                href="{{ $shares->nextPageUrl() }}"
                            >
                                Next
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection