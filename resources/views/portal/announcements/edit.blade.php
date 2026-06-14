@extends('portal.layouts.app')

@section('content')
@include('portal.announcements._style')

<div class="announcement-page">
    <div class="announcement-header">
        <div>
            <h1>Edit Announcement</h1>

            <p>
                Update announcement information and manage images.
            </p>
        </div>

        <a
            href="{{ route('portal.announcements.index') }}"
            class="btn btn-light"
        >
            ← Back
        </a>
    </div>

    @if(session('success'))
        <div class="form-alert success">
            {{ session('success') }}
        </div>
    @endif

    <div class="announcement-card">
        <div class="announcement-card-header">
            <h3>Announcement Information</h3>
        </div>

        <div class="announcement-card-body">
            <form
                method="POST"
                action="{{ route(
                    'portal.announcements.update',
                    $announcement
                ) }}"
                enctype="multipart/form-data"
            >
                @method('PUT')

                @include('portal.announcements._form', [
                    'buttonText' => 'Update Announcement',
                ])
            </form>

            @foreach($announcement->images as $image)
                <form
                    id="delete-image-{{ $image->id }}"
                    method="POST"
                    action="{{ route(
                        'portal.announcements.images.delete',
                        [$announcement, $image]
                    ) }}"
                    style="display:none"
                >
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach
        </div>
    </div>
</div>
@endsection