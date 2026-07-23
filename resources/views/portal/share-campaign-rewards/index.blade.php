@extends('portal.layouts.app')

@push('styles')
    @include('portal.share-campaigns._styles')
@endpush

@section('title', 'Share Campaign Rewards')

@section('content')
    <div class="campaign-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    Share Campaign Rewards
                </h1>

                <p class="page-description">
                    Review user shares and manually give
                    campaign spins.
                </p>
            </div>

            <a
                class="button button-secondary"
                href="{{ route(
                    'portal.share-campaigns.index'
                ) }}"
            >
                Back to Campaigns
            </a>
        </div>

        {{-- Success message --}}
        @if (session('success'))
            <div class="success">
                {{ session('success') }}
            </div>
        @endif

        {{-- General error message --}}
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
                        The reward could not be processed:
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

        {{-- Filters --}}
        <div class="campaign-card">
            <form
                method="GET"
                action="{{ route(
                    'portal.share-campaign-rewards.index'
                ) }}"
            >
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="search">
                            Search
                        </label>

                        <input
                            id="search"
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Phone, user or campaign"
                        >
                    </div>

                    <div class="filter-group">
                        <label for="campaign_id">
                            Campaign
                        </label>

                        <select
                            id="campaign_id"
                            name="campaign_id"
                        >
                            <option value="">
                                All campaigns
                            </option>

                            @foreach ($campaigns as $campaign)
                                <option
                                    value="{{ $campaign->id }}"
                                    @selected(
                                        (int) $campaignId
                                        === (int) $campaign->id
                                    )
                                >
                                    {{ $campaign->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="status">
                            Reward Status
                        </label>

                        <select
                            id="status"
                            name="status"
                        >
                            <option value="">
                                All statuses
                            </option>

                            <option
                                value="pending"
                                @selected(
                                    $status === 'pending'
                                )
                            >
                                Pending
                            </option>

                            <option
                                value="granted"
                                @selected(
                                    $status === 'granted'
                                )
                            >
                                Granted
                            </option>

                            <option
                                value="not_eligible"
                                @selected(
                                    $status === 'not_eligible'
                                )
                            >
                                Not Eligible
                            </option>
                        </select>
                    </div>

                    <div class="actions">
                        <button
                            class="button button-primary"
                            type="submit"
                        >
                            Search
                        </button>

                        <a
                            class="button button-secondary"
                            href="{{ route(
                                'portal.share-campaign-rewards.index'
                            ) }}"
                        >
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- Reward table --}}
        <div class="campaign-card">
            <div class="table-wrapper">
                <table class="campaign-table">
                    <thead>
                        <tr>
                            <th>User Phone</th>
                            <th>Campaign</th>
                            <th>Facebook Post</th>
                            <th>Verified Shares</th>
                            <th>Required Shares</th>
                            <th>Reward Spins</th>
                            <th>Status</th>
                            <th>Granted Information</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                {{-- User --}}
                                <td>
                                    <strong>
                                        {{ $row->phone_number }}
                                    </strong>

                                    @if (
                                        $row->user_name
                                        && $row->user_name !== '-'
                                    )
                                        <div class="muted">
                                            {{ $row->user_name }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Campaign --}}
                                <td>
                                    <strong>
                                        {{ $row->campaign_title }}
                                    </strong>

                                    <div class="muted">
                                        Campaign ID:
                                        {{ $row->campaign_id }}
                                    </div>

                                    @if ($row->reward_repeatable)
                                        <span
                                            class="badge badge-warning"
                                        >
                                            Repeatable
                                        </span>
                                    @else
                                        <span
                                            class="badge badge-gray"
                                        >
                                            One time
                                        </span>
                                    @endif
                                </td>

                                {{-- Facebook URL --}}
                                <td>
                                    @if ($row->facebook_post_url)
                                        <a
                                            class="
                                                button
                                                button-small
                                                button-secondary
                                            "
                                            href="{{
                                                $row->facebook_post_url
                                            }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            View Post
                                        </a>
                                    @else
                                        <span class="muted">
                                            No link
                                        </span>
                                    @endif
                                </td>

                                {{-- Share progress --}}
                                <td>
                                    <strong>
                                        {{ number_format(
                                            $row->verified_shares
                                        ) }}
                                    </strong>
                                </td>

                                <td>
                                    {{ number_format(
                                        $row->required_shares
                                    ) }}
                                </td>

                                {{-- Reward amount --}}
                                <td>
                                    <strong>
                                        {{ number_format(
                                            $row->reward_spins
                                        ) }}
                                    </strong>
                                    spins
                                </td>

                                {{-- Reward status --}}
                                <td>
                                    @if (
                                        $row->reward_status
                                        === 'pending'
                                    )
                                        <span
                                            class="badge badge-warning"
                                        >
                                            Pending
                                        </span>

                                        @if (
                                            $row->pending_milestones > 1
                                        )
                                            <div class="muted">
                                                Pending rewards:
                                                {{
                                                    $row
                                                        ->pending_milestones
                                                }}
                                            </div>
                                        @endif
                                    @elseif (
                                        $row->reward_status
                                        === 'granted'
                                    )
                                        <span
                                            class="badge badge-success"
                                        >
                                            Granted
                                        </span>

                                        <div class="muted">
                                            Granted rewards:
                                            {{
                                                $row
                                                    ->granted_milestones
                                            }}
                                        </div>
                                    @else
                                        <span
                                            class="badge badge-gray"
                                        >
                                            Not Eligible
                                        </span>
                                    @endif
                                </td>

                                {{-- Granted information --}}
                                <td>
                                    @if ($row->last_awarded_at)
                                        <div>
                                            {{
                                                $row
                                                    ->last_awarded_at
                                                    ->format(
                                                        'd M Y H:i'
                                                    )
                                            }}
                                        </div>

                                        <div class="muted">
                                            Granted by:
                                            {{
                                                $row
                                                    ->last_granted_by
                                                ?? 'System / legacy'
                                            }}
                                        </div>
                                    @else
                                        <span class="muted">
                                            Not granted yet
                                        </span>
                                    @endif
                                </td>

                                {{-- Manual Give Spin button --}}
                                <td>
                                    @if (
                                        $row->eligible_for_reward
                                    )
                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'portal.share-campaign-rewards.grant',
                                                [
                                                    'progress' =>
                                                        $row
                                                            ->progress_id,
                                                ]
                                            ) }}"
                                            onsubmit="
                                                return confirm(
                                                    'Are you sure you want to give this spin reward?'
                                                );
                                            "
                                        >
                                            @csrf

                                            <button
                                                class="
                                                    button
                                                    button-small
                                                    button-success
                                                "
                                                type="submit"
                                            >
                                                Give
                                                {{
                                                    number_format(
                                                        $row
                                                            ->reward_spins
                                                    )
                                                }}
                                                Spins
                                            </button>
                                        </form>
                                    @elseif (
                                        $row->reward_status
                                        === 'granted'
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
                                            Granted
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
                                            Not Eligible
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="9"
                                    class="empty-state"
                                >
                                    No campaign share rewards found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($rows->hasPages())
                <div class="pagination-area">
                    <div class="muted">
                        Showing
                        {{ $rows->firstItem() }}
                        to
                        {{ $rows->lastItem() }}
                        of
                        {{ $rows->total() }}
                        records
                    </div>

                    <div class="actions">
                        @if ($rows->previousPageUrl())
                            <a
                                class="
                                    button
                                    button-secondary
                                "
                                href="{{
                                    $rows->previousPageUrl()
                                }}"
                            >
                                Previous
                            </a>
                        @endif

                        @if ($rows->nextPageUrl())
                            <a
                                class="
                                    button
                                    button-secondary
                                "
                                href="{{
                                    $rows->nextPageUrl()
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