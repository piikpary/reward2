@extends('portal.layouts.app')

@push('styles')
    @include('portal.share-campaigns._styles')
@endpush

@section('title', 'Share Campaigns')

@section('content')
    <div class="campaign-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    Share Campaigns
                </h1>

                <p class="page-description">
                    Create campaigns, publish posters,
                    and monitor customer sharing.
                </p>
            </div>

            <div class="actions">
                    <a
                        class="button button-secondary"
                        href="{{ route(
                            'portal.share-campaign-rewards.index'
                        ) }}"
                    >
                        Share Rewards
                    </a>

                    <a
                        class="button button-primary"
                        href="{{ route(
                            'portal.share-campaigns.create'
                        ) }}"
                    >
                        Create Campaign
                    </a>
                </div>
        </div>

        @if (session('success'))
            <div class="success">
                {{ session('success') }}
            </div>
        @endif

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

        {{-- Search and filtering --}}
        <div class="campaign-card">
            <form
                method="GET"
                action="{{ route(
                    'portal.share-campaigns.index'
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
                            placeholder="Campaign title or description"
                        >
                    </div>

                    <div class="filter-group">
                        <label for="status">
                            Status
                        </label>

                        <select
                            id="status"
                            name="status"
                        >
                            <option value="">
                                All campaigns
                            </option>

                            <option
                                value="active"
                                @selected(
                                    $status === 'active'
                                )
                            >
                                Active
                            </option>

                            <option
                                value="inactive"
                                @selected(
                                    $status === 'inactive'
                                )
                            >
                                Inactive
                            </option>

                            <option
                                value="published"
                                @selected(
                                    $status === 'published'
                                )
                            >
                                Published
                            </option>

                            <option
                                value="draft"
                                @selected(
                                    $status === 'draft'
                                )
                            >
                                Draft
                            </option>

                            <option
                                value="expired"
                                @selected(
                                    $status === 'expired'
                                )
                            >
                                Expired
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
                                'portal.share-campaigns.index'
                            ) }}"
                        >
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- Campaign table --}}
        <div class="campaign-card">
            <div class="table-wrapper">
                <table class="campaign-table">
                    <thead>
                        <tr>
                            <th>Poster</th>
                            <th>Campaign</th>
                            <th>Reward rule</th>
                            <th>Shares</th>
                            <th>Rewards</th>
                            <th>Schedule</th>
                            <th>Status</th>
                            <th>Publication</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($campaigns as $campaign)
                            <tr>
                                <td>
                                    @if ($campaign->imageUrl())
                                        <img
                                            class="poster"
                                            src="{{ $campaign->imageUrl() }}"
                                            alt="{{ $campaign->title }}"
                                        >
                                    @else
                                        <span class="muted">
                                            No image
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <strong>
                                        {{ $campaign->title }}
                                    </strong>

                                    <div class="muted">
                                        Campaign ID:
                                        {{ $campaign->id }}
                                    </div>

                                    @if ($campaign->business_id)
                                        <div class="muted">
                                            Business:
                                            {{ $campaign->business_id }}
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    <strong>
                                        {{ number_format(
                                            $campaign->required_shares
                                        ) }}
                                    </strong>
                                    shares

                                    <br>

                                    <strong>
                                        {{ number_format(
                                            $campaign->reward_spins
                                        ) }}
                                    </strong>
                                    spins

                                    @if ($campaign->reward_repeatable)
                                        <br>

                                        <span
                                            class="badge badge-warning"
                                        >
                                            Repeatable
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{ number_format(
                                        $campaign->shares_count
                                    ) }}
                                </td>

                                <td>
                                    {{ number_format(
                                        $campaign->rewards_count
                                    ) }}
                                </td>

                                <td>
                                    <div>
                                        Start:

                                        {{
                                            $campaign->starts_at
                                                ?->format('d M Y H:i')
                                            ?? 'Immediately'
                                        }}
                                    </div>

                                    <div>
                                        End:

                                        {{
                                            $campaign->expires_at
                                                ?->format('d M Y H:i')
                                            ?? 'No expiry'
                                        }}
                                    </div>

                                    @if (
                                        $campaign->expires_at
                                        && $campaign->expires_at->isPast()
                                    )
                                        <span
                                            class="badge badge-danger"
                                        >
                                            Expired
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if ($campaign->is_active)
                                        <span
                                            class="badge badge-success"
                                        >
                                            Active
                                        </span>
                                    @else
                                        <span
                                            class="badge badge-danger"
                                        >
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if ($campaign->is_published)
                                        <span
                                            class="badge badge-success"
                                        >
                                            Published
                                        </span>
                                    @else
                                        <span
                                            class="badge badge-gray"
                                        >
                                            Draft
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="actions">
                                        <a
                                            class="
                                                button
                                                button-small
                                                button-secondary
                                            "
                                            href="{{ route(
                                                'portal.share-campaigns.edit',
                                                $campaign
                                            ) }}"
                                        >
                                            Edit
                                        </a>

                                        <a
                                            class="
                                                button
                                                button-small
                                                button-primary
                                            "
                                            href="{{ route(
                                                'portal.share-campaigns.shares',
                                                $campaign
                                            ) }}"
                                        >
                                            Shares
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'portal.share-campaigns.toggle-active',
                                                $campaign
                                            ) }}"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                class="
                                                    button
                                                    button-small
                                                    {{
                                                        $campaign->is_active
                                                            ? 'button-warning'
                                                            : 'button-success'
                                                    }}
                                                "
                                                type="submit"
                                            >
                                                {{
                                                    $campaign->is_active
                                                        ? 'Deactivate'
                                                        : 'Activate'
                                                }}
                                            </button>
                                        </form>

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'portal.share-campaigns.toggle-publish',
                                                $campaign
                                            ) }}"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                class="
                                                    button
                                                    button-small
                                                    {{
                                                        $campaign->is_published
                                                            ? 'button-warning'
                                                            : 'button-success'
                                                    }}
                                                "
                                                type="submit"
                                            >
                                                {{
                                                    $campaign->is_published
                                                        ? 'Unpublish'
                                                        : 'Publish'
                                                }}
                                            </button>
                                        </form>

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'portal.share-campaigns.destroy',
                                                $campaign
                                            ) }}"
                                            onsubmit="
                                                return confirm(
                                                    'Are you sure you want to delete this campaign?'
                                                );
                                            "
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                class="
                                                    button
                                                    button-small
                                                    button-danger
                                                "
                                                type="submit"
                                            >
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="9"
                                    class="empty-state"
                                >
                                    No share campaigns found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($campaigns->hasPages())
                <div class="pagination-area">
                    <div class="muted">
                        Showing
                        {{ $campaigns->firstItem() }}
                        to
                        {{ $campaigns->lastItem() }}
                        of
                        {{ $campaigns->total() }}
                        campaigns
                    </div>

                    <div class="actions">
                        @if ($campaigns->previousPageUrl())
                            <a
                                class="
                                    button
                                    button-secondary
                                "
                                href="{{
                                    $campaigns->previousPageUrl()
                                }}"
                            >
                                Previous
                            </a>
                        @endif

                        @if ($campaigns->nextPageUrl())
                            <a
                                class="
                                    button
                                    button-secondary
                                "
                                href="{{
                                    $campaigns->nextPageUrl()
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