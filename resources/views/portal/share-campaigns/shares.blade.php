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
            box-shadow:
                0 10px 30px
                rgba(0, 0, 0, 0.05);
        }

        .campaign-summary-grid {
            display: grid;
            grid-template-columns:
                220px minmax(0, 1fr);
            gap: 28px;
            align-items: start;
        }

        .campaign-poster-box {
            width: 100%;
        }

        .campaign-poster-box .poster-large {
            width: 100%;
            height: 170px;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            background: #f7f8fa;
            object-fit: cover;
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
            grid-template-columns:
                repeat(4, minmax(0, 1fr));
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
            grid-template-columns:
                repeat(7, minmax(0, 1fr));
            gap: 14px;
        }

        .statistics-report .stat {
            padding: 20px;
            border: 1px solid #eeeeee;
            border-radius: 18px;
            background: #ffffff;
            box-shadow:
                0 8px 24px
                rgba(0, 0, 0, 0.04);
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
            font-size: 36px;
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

        .report-filter {
            padding: 18px 24px;
            border-bottom: 1px solid #eeeeee;
            background: #ffffff;
        }

        .report-filter-grid {
            display: grid;
            grid-template-columns:
                repeat(2, minmax(160px, 1fr))
                minmax(220px, 1.4fr)
                minmax(190px, 1fr)
                auto;
            gap: 14px;
            align-items: end;
        }

        .report-filter-field {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .report-filter-label {
            color: #374151;
            font-size: 12px;
            font-weight: 700;
        }

        .report-filter-input {
            width: 100%;
            min-height: 42px;
            padding: 9px 12px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            background: #ffffff;
            color: #111827;
            font-size: 14px;
        }

        .report-filter-input:focus {
            border-color: #2563eb;
            outline: none;
            box-shadow:
                0 0 0 3px
                rgba(37, 99, 235, 0.12);
        }

        .report-filter-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .share-date-group-row td {
            padding: 14px 16px !important;
            border-top: 1px solid #dbeafe;
            border-bottom: 1px solid #dbeafe;
            background: #eff6ff;
        }

        .share-date-group-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .share-date-group-label {
            color: #1e3a8a;
            font-size: 15px;
            font-weight: 800;
        }

        .share-date-group-count {
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
        }

        .report-table-card .table-wrapper {
            padding: 0 18px 18px;
        }

        .campaign-table.report-table {
            min-width: 1450px;
        }

        .campaign-table.report-table th {
            background: #f8fafc;
        }

        .campaign-table.report-table td {
            padding-top: 16px;
            padding-bottom: 16px;
            vertical-align: top;
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

        .review-actions {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            flex-wrap: wrap;
        }

        .review-actions form {
            margin: 0;
        }

        .review-reason {
            margin-top: 6px;
            padding: 8px 10px;
            border: 1px solid #fecaca;
            border-radius: 8px;
            background: #fef2f2;
            color: #991b1b;
            font-size: 12px;
            line-height: 1.5;
        }

        @media (max-width: 1450px) {
            .statistics.statistics-report {
                grid-template-columns:
                    repeat(4, minmax(0, 1fr));
            }
        }

        @media (max-width: 1200px) {
            .report-filter-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

            .campaign-meta-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

            .statistics.statistics-report {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
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
            .report-filter-grid {
                grid-template-columns: 1fr;
            }

            .report-filter-actions {
                width: 100%;
            }

            .report-filter-actions .button {
                flex: 1;
            }

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
                    Review submitted Facebook posts,
                    approve valid public campaign posts,
                    or reject invalid submissions.
                </p>
            </div>

            <div class="report-actions">
                <a
                    class="button button-secondary"
                    href="{{ route(
                        'portal.share-campaigns.index'
                    ) }}"
                >
                    Campaign List
                </a>

                <a
                    class="button button-primary"
                    href="{{ route(
                        'portal.share-campaigns.edit',
                        $shareCampaign
                    ) }}"
                >
                    Edit Campaign
                </a>
            </div>
        </div>

        {{-- Success message --}}
        @if (session('success'))
            <div class="success">
                {{ session('success') }}
            </div>
        @endif

        {{-- General error --}}
        @if (session('error'))
            <div class="campaign-card">
                <div class="error">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        {{-- Validation errors --}}
        @if ($errors->any())
            <div class="campaign-card">
                <div class="error">
                    <strong>
                        Please correct the following errors:
                    </strong>

                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>
                                {{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Campaign information --}}
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
                            {{
                                $shareCampaign->description
                                ?: 'No campaign description provided.'
                            }}
                        </p>
                    </div>

                    <div class="campaign-meta-grid">
                        <div class="campaign-meta-item">
                            <div class="campaign-meta-label">
                                Required shares
                            </div>

                            <div class="campaign-meta-value">
                                {{
                                    number_format(
                                        $shareCampaign
                                            ->required_shares
                                    )
                                }}
                            </div>
                        </div>

                        <div class="campaign-meta-item">
                            <div class="campaign-meta-label">
                                Reward spins
                            </div>

                            <div class="campaign-meta-value">
                                {{
                                    number_format(
                                        $shareCampaign
                                            ->reward_spins
                                    )
                                }}
                            </div>
                        </div>

                        <div class="campaign-meta-item">
                            <div class="campaign-meta-label">
                                Start date
                            </div>

                            <div
                                class="campaign-meta-value"
                                style="font-size: 16px;"
                            >
                                {{
                                    $shareCampaign
                                        ->starts_at
                                        ?->format(
                                            'd M Y H:i'
                                        )
                                    ?? 'Immediately'
                                }}
                            </div>
                        </div>

                        <div class="campaign-meta-item">
                            <div class="campaign-meta-label">
                                Expiry date
                            </div>

                            <div
                                class="campaign-meta-value"
                                style="font-size: 16px;"
                            >
                                {{
                                    $shareCampaign
                                        ->expires_at
                                        ?->format(
                                            'd M Y H:i'
                                        )
                                    ?? 'No expiry'
                                }}
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
                                Repeatable Reward
                            </span>
                        @else
                            <span class="badge badge-gray">
                                One-time Reward
                            </span>
                        @endif

                        @if (
                            $shareCampaign->expires_at
                            && $shareCampaign
                                ->expires_at
                                ->isPast()
                        )
                            <span class="badge badge-danger">
                                Expired
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Statistics --}}
        <div class="statistics statistics-report">
            <div class="stat">
                <div class="stat-label">
                    Total submissions
                </div>

                <div class="stat-value">
                    {{
                        number_format(
                            $statistics['total_shares']
                        )
                    }}
                </div>

                <div class="stat-note">
                    All Facebook links submitted for review.
                </div>
            </div>

            <div class="stat">
                <div class="stat-label">
                    Pending review
                </div>

                <div class="stat-value">
                    {{
                        number_format(
                            $statistics['pending_shares']
                        )
                    }}
                </div>

                <div class="stat-note">
                    Submissions waiting for admin review.
                </div>
            </div>

            <div class="stat">
                <div class="stat-label">
                    Approved shares
                </div>

                <div class="stat-value">
                    {{
                        number_format(
                            $statistics['verified_shares']
                        )
                    }}
                </div>

                <div class="stat-note">
                    Public campaign posts approved by admin.
                </div>
            </div>

            <div class="stat">
                <div class="stat-label">
                    Rejected shares
                </div>

                <div class="stat-value">
                    {{
                        number_format(
                            $statistics['rejected_shares']
                        )
                    }}
                </div>

                <div class="stat-note">
                    Invalid, private, or unrelated posts.
                </div>
            </div>

            <div class="stat">
                <div class="stat-label">
                    Unique customers
                </div>

                <div class="stat-value">
                    {{
                        number_format(
                            $statistics['unique_customers']
                        )
                    }}
                </div>

                <div class="stat-note">
                    Customers participating in this campaign.
                </div>
            </div>

            <div class="stat">
                <div class="stat-label">
                    Rewards granted
                </div>

                <div class="stat-value">
                    {{
                        number_format(
                            $statistics['rewards_awarded']
                        )
                    }}
                </div>

                <div class="stat-note">
                    Rewards manually granted by admins.
                </div>
            </div>

            <div class="stat">
                <div class="stat-label">
                    Spins granted
                </div>

                <div class="stat-value">
                    {{
                        number_format(
                            $statistics['spins_awarded']
                        )
                    }}
                </div>

                <div class="stat-note">
                    Total spins added to user accounts.
                </div>
            </div>
        </div>

        {{-- Share review table --}}
        <div class="campaign-card report-table-card">
            <div class="report-table-header">
                <h2 class="report-table-title">
                    Customer Share Submissions
                </h2>

                <p class="report-table-subtitle">
                    Review submitted Facebook posts,
                    approve valid public campaign posts,
                    or reject invalid submissions.
                </p>
            </div>

            <div class="report-filter">
                <form
                    method="GET"
                    action="{{ url()->current() }}"
                >
                    <div class="report-filter-grid">
                        <div class="report-filter-field">
                            <label
                                class="report-filter-label"
                                for="date_from"
                            >
                                Date From
                            </label>

                            <input
                                class="report-filter-input"
                                type="date"
                                id="date_from"
                                name="date_from"
                                value="{{ request('date_from') }}"
                            >
                        </div>

                        <div class="report-filter-field">
                            <label
                                class="report-filter-label"
                                for="date_to"
                            >
                                Date To
                            </label>

                            <input
                                class="report-filter-input"
                                type="date"
                                id="date_to"
                                name="date_to"
                                value="{{ request('date_to') }}"
                            >
                        </div>

                        <div class="report-filter-field">
                            <label
                                class="report-filter-label"
                                for="user_id"
                            >
                                Customer
                            </label>

                            <select
                                class="report-filter-input"
                                id="user_id"
                                name="user_id"
                            >
                                <option value="">
                                    All Customers
                                </option>

                                @foreach ($users as $filterUser)
                                    <option
                                        value="{{ $filterUser->id }}"
                                        @selected(
                                            (string) request('user_id')
                                            === (string) $filterUser->id
                                        )
                                    >
                                        {{
                                            $filterUser->name
                                            ?: 'Customer #'
                                                . $filterUser->id
                                        }}
                                        —
                                        {{
                                            $filterUser->phone_number
                                            ?: 'No phone'
                                        }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="report-filter-field">
                            <label
                                class="report-filter-label"
                                for="review_status"
                            >
                                Review Status
                            </label>

                            <select
                                class="report-filter-input"
                                id="review_status"
                                name="review_status"
                            >
                                <option value="">
                                    All Statuses
                                </option>

                                <option
                                    value="pending"
                                    @selected(
                                        request('review_status')
                                        === 'pending'
                                    )
                                >
                                    Pending Review
                                </option>

                                <option
                                    value="verified"
                                    @selected(
                                        request('review_status')
                                        === 'verified'
                                    )
                                >
                                    Approved
                                </option>

                                <option
                                    value="rejected"
                                    @selected(
                                        request('review_status')
                                        === 'rejected'
                                    )
                                >
                                    Rejected
                                </option>
                            </select>
                        </div>

                        <div class="report-filter-actions">
                            <button
                                class="button button-primary"
                                type="submit"
                            >
                                Filter
                            </button>

                            <a
                                class="button button-secondary"
                                href="{{ url()->current() }}"
                            >
                                Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-wrapper">
                <table class="campaign-table report-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Facebook Post</th>
                            <th>Submitted At</th>
                            <th>Review Status</th>
                            <th>Reviewed Information</th>
                            <th>IP Address</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php
                            $rowNumber =
                                ($shares->currentPage() - 1)
                                * $shares->perPage();
                        @endphp

                        @forelse ($groupedShares as $dateKey => $dateShares)
                            @php
                                if ($dateKey === 'unknown') {
                                    $dateLabel = 'Unknown Date';
                                } else {
                                    $groupDate =
                                        \Illuminate\Support\Carbon
                                            ::parse($dateKey);

                                    if ($groupDate->isToday()) {
                                        $dateLabel = 'Today';
                                    } elseif (
                                        $groupDate->isYesterday()
                                    ) {
                                        $dateLabel = 'Yesterday';
                                    } else {
                                        $dateLabel =
                                            $groupDate->format(
                                                'l d F Y'
                                            );
                                    }
                                }
                            @endphp

                            <tr class="share-date-group-row">
                                <td colspan="9">
                                    <div
                                        class="
                                            share-date-group-content
                                        "
                                    >
                                        <span
                                            class="
                                                share-date-group-label
                                            "
                                        >
                                            {{ $dateLabel }}
                                        </span>

                                        <span
                                            class="
                                                share-date-group-count
                                            "
                                        >
                                            {{
                                                $dateShares->count()
                                            }}
                                            {{
                                                $dateShares->count()
                                                === 1
                                                    ? 'submission'
                                                    : 'submissions'
                                            }}
                                        </span>
                                    </div>
                                </td>
                            </tr>

                            @foreach ($dateShares as $share)
                                @php
                                    $rowNumber++;

                                    $customerName =
                                        $share->user?->name
                                        ?? $share->user?->phone_number
                                        ?? 'Customer #' . $share->user_id;

                                    $customerPhone =
                                        $share->user?->phone_number
                                        ?? 'N/A';
                                @endphp

                                <tr>
                                    <td>
                                        <div class="table-cell-main">
                                            {{ $rowNumber }}
                                        </div>
                                    </td>

                                    <td>
                                        <div class="table-cell-main">
                                            {{ $customerName }}
                                        </div>

                                        <div class="table-cell-sub">
                                            User ID:
                                            {{ $share->user_id }}
                                        </div>
                                    </td>

                                    <td>
                                        <div class="table-cell-main">
                                            {{ $customerPhone }}
                                        </div>
                                    </td>

                                    <td>
                                        <a
                                            class="facebook-link"
                                            href="{{
                                                $share
                                                    ->facebook_post_url
                                            }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            Open Facebook Post
                                        </a>

                                        <div class="table-cell-sub">
                                            {{
                                                \Illuminate\Support\Str
                                                    ::limit(
                                                        $share
                                                            ->facebook_post_url,
                                                        60
                                                    )
                                            }}
                                        </div>
                                    </td>

                                    <td>
                                        <div class="table-cell-main">
                                            {{
                                                $share
                                                    ->shared_at
                                                    ?->format(
                                                        'd M Y H:i:s'
                                                    )
                                                ?? '-'
                                            }}
                                        </div>
                                    </td>

                                    <td>
                                        @if (
                                            $share->status ===
                                            \App\Models\CampaignShare
                                                ::STATUS_PENDING
                                        )
                                            <span
                                                class="
                                                    badge
                                                    badge-warning
                                                "
                                            >
                                                Pending Review
                                            </span>
                                        @elseif (
                                            $share->status ===
                                            \App\Models\CampaignShare
                                                ::STATUS_VERIFIED
                                        )
                                            <span
                                                class="
                                                    badge
                                                    badge-success
                                                "
                                            >
                                                Approved
                                            </span>
                                        @elseif (
                                            $share->status ===
                                            \App\Models\CampaignShare
                                                ::STATUS_REJECTED
                                        )
                                            <span
                                                class="
                                                    badge
                                                    badge-danger
                                                "
                                            >
                                                Rejected
                                            </span>
                                        @else
                                            <span
                                                class="
                                                    badge
                                                    badge-gray
                                                "
                                            >
                                                {{
                                                    ucfirst(
                                                        $share->status
                                                    )
                                                }}
                                            </span>
                                        @endif

                                        <div class="table-cell-sub">
                                            Method:
                                            {{
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    ucfirst(
                                                        $share
                                                            ->verification_method
                                                    )
                                                )
                                            }}
                                        </div>
                                    </td>

                                    <td>
                                        @if ($share->reviewed_at)
                                            <div class="table-cell-main">
                                                {{
                                                    $share
                                                        ->reviewed_at
                                                        ->format(
                                                            'd M Y H:i'
                                                        )
                                                }}
                                            </div>

                                            <div class="table-cell-sub">
                                                Reviewed by:
                                                {{
                                                    $share
                                                        ->reviewedBy
                                                        ?->name
                                                    ?? 'Unknown admin'
                                                }}
                                            </div>

                                            @if (
                                                $share
                                                    ->rejection_reason
                                            )
                                                <div class="review-reason">
                                                    <strong>
                                                        Reason:
                                                    </strong>

                                                    {{
                                                        $share
                                                            ->rejection_reason
                                                    }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="muted">
                                                Not reviewed yet
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="table-cell-main">
                                            {{
                                                $share->ip_address
                                                ?? 'N/A'
                                            }}
                                        </div>
                                    </td>

                                    <td>
                                        @if (
                                            $share->status ===
                                            \App\Models\CampaignShare
                                                ::STATUS_PENDING
                                        )
                                            <div class="review-actions">
                                                <form
                                                    method="POST"
                                                    action="{{ route(
                                                        'portal.share-campaigns.shares.approve',
                                                        [
                                                            'shareCampaign' =>
                                                                $shareCampaign,

                                                            'share' =>
                                                                $share,
                                                        ]
                                                    ) }}"
                                                    onsubmit="
                                                        return confirm(
                                                            'Approve this Facebook post?'
                                                        );
                                                    "
                                                >
                                                    @csrf
                                                    @method('PATCH')

                                                    <button
                                                        class="
                                                            button
                                                            button-small
                                                            button-success
                                                        "
                                                        type="submit"
                                                    >
                                                        Approve
                                                    </button>
                                                </form>

                                                <form
                                                    method="POST"
                                                    action="{{ route(
                                                        'portal.share-campaigns.shares.reject',
                                                        [
                                                            'shareCampaign' =>
                                                                $shareCampaign,

                                                            'share' =>
                                                                $share,
                                                        ]
                                                    ) }}"
                                                    onsubmit="
                                                        return confirm(
                                                            'Reject this Facebook post?'
                                                        );
                                                    "
                                                >
                                                    @csrf
                                                    @method('PATCH')

                                                    <input
                                                        type="hidden"
                                                        name="rejection_reason"
                                                        value="The post is not public or does not match the campaign."
                                                    >

                                                    <button
                                                        class="
                                                            button
                                                            button-small
                                                            button-danger
                                                        "
                                                        type="submit"
                                                    >
                                                        Reject
                                                    </button>
                                                </form>
                                            </div>
                                        @elseif (
                                            $share->status ===
                                            \App\Models\CampaignShare
                                                ::STATUS_VERIFIED
                                        )
                                            <button
                                                class="
                                                    button
                                                    button-small
                                                    button-secondary
                                                "
                                                type="button"
                                                disabled
                                            >
                                                Approved
                                            </button>
                                        @else
                                            <button
                                                class="
                                                    button
                                                    button-small
                                                    button-secondary
                                                "
                                                type="button"
                                                disabled
                                            >
                                                Rejected
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td
                                    colspan="9"
                                    class="empty-report"
                                >
                                    <strong>
                                        No customer share
                                        submissions yet
                                    </strong>

                                    Submitted Facebook post links
                                    will appear here for manual
                                    review.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($shares->hasPages())
                <div
                    class="pagination-area"
                    style="padding: 0 24px 24px;"
                >
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
                                class="
                                    button
                                    button-secondary
                                "
                                href="{{
                                    $shares
                                        ->previousPageUrl()
                                }}"
                            >
                                Previous
                            </a>
                        @endif

                        @if ($shares->nextPageUrl())
                            <a
                                class="
                                    button
                                    button-secondary
                                "
                                href="{{
                                    $shares
                                        ->nextPageUrl()
                                }}"
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