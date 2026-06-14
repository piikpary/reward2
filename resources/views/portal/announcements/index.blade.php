@extends('portal.layouts.app')

@section('content')
@include('portal.announcements._style')

<div class="announcement-page">
    <div class="announcement-header">
        <div>
            <h1>Announcements</h1>

            <p>
                Manage announcements displayed in the mobile application.
            </p>
        </div>

        <a
            href="{{ route('portal.announcements.create') }}"
            class="btn btn-dark"
        >
            + Add Announcement
        </a>
    </div>

    @if(session('success'))
        <div class="form-alert success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="form-alert error">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="announcement-stats">
        <div class="announcement-stat">
            <span>Total Announcements</span>
            <strong>
                {{ number_format($totalAnnouncements) }}
            </strong>
        </div>

        <div class="announcement-stat">
            <span>Active Announcements</span>
            <strong>
                {{ number_format($activeAnnouncements) }}
            </strong>
        </div>

        <div class="announcement-stat">
            <span>Published Now</span>
            <strong>
                {{ number_format($publishedAnnouncements) }}
            </strong>
        </div>
    </div>

    <div class="announcement-card">
        <div class="announcement-card-body">
            <form
                method="GET"
                action="{{ route('portal.announcements.index') }}"
                class="announcement-filters"
            >
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search announcement title or content..."
                >

                <select name="status">
                    <option value="">All Status</option>

                    <option
                        value="active"
                        @selected($status === 'active')
                    >
                        Active
                    </option>

                    <option
                        value="inactive"
                        @selected($status === 'inactive')
                    >
                        Inactive
                    </option>
                </select>

                <button
                    type="submit"
                    class="btn btn-dark"
                >
                    Search
                </button>

                <a
                    href="{{ route('portal.announcements.index') }}"
                    class="btn btn-light"
                >
                    Reset
                </a>
            </form>

            <div class="announcement-table-wrap">
                <table class="announcement-table">
                    <thead>
                        <tr>
                            <th>Announcement</th>
                            <th>Images</th>
                            <th>Publication Date</th>
                            <th>Publish State</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($announcements as $announcement)
                            <tr>
                                <td>
                                    <div class="announcement-title-cell">
                                        @if($announcement->images->first())
                                            <img
                                                src="{{ $announcement->images->first()->image_url }}"
                                                alt="{{ $announcement->title }}"
                                                class="announcement-thumb"
                                            >
                                        @endif

                                        <div>
                                            <strong>
                                                {{ $announcement->title }}
                                            </strong>

                                            <small>
                                                {{ \Illuminate\Support\Str::limit(
                                                    $announcement->content,
                                                    70
                                                ) }}
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    {{ $announcement->images->count() }}
                                </td>

                                <td>
                                    {{ $announcement->announcement_date
                                        ?->format('d M Y, h:i A') }}
                                </td>

                                <td>
                                    @if(
                                        $announcement->announcement_date
                                        && $announcement->announcement_date->isFuture()
                                    )
                                        <span class="badge scheduled">
                                            Scheduled
                                        </span>
                                    @else
                                        <span class="badge active">
                                            Published
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if($announcement->status === 'active')
                                        <span class="badge active">
                                            Active
                                        </span>
                                    @else
                                        <span class="badge inactive">
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="table-actions">
                                        <a
                                            href="{{ route(
                                                'portal.announcements.edit',
                                                $announcement
                                            ) }}"
                                            class="btn btn-sm btn-dark"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'portal.announcements.toggle-status',
                                                $announcement
                                            ) }}"
                                            class="inline-form"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-blue"
                                            >
                                                {{ $announcement->status === 'active'
                                                    ? 'Inactive'
                                                    : 'Active' }}
                                            </button>
                                        </form>

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'portal.announcements.destroy',
                                                $announcement
                                            ) }}"
                                            class="inline-form"
                                            onsubmit="return confirm(
                                                'Delete this announcement and all its images?'
                                            )"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-danger"
                                            >
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        No announcements found.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 20px;">
                {{ $announcements->links() }}
            </div>
        </div>
    </div>
</div>
@endsection