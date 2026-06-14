@extends('portal.layouts.app')

@section('content')
@include('portal.announcements._style')

<div class="announcement-page">
    <div class="announcement-header">
        <div>
            <h1>Add Announcement</h1>

            <p>
                Create a new mobile announcement with multiple images.
            </p>
        </div>

        <a
            href="{{ route('portal.announcements.index') }}"
            class="btn btn-light"
        >
            ← Back
        </a>
    </div>

    <div class="announcement-card">
        <div class="announcement-card-header">
            <h3>Announcement Information</h3>
        </div>

        <div class="announcement-card-body">
            <form
                method="POST"
                action="{{ route('portal.announcements.store') }}"
                enctype="multipart/form-data"
            >
                @include('portal.announcements._form', [
                    'buttonText' => 'Save Announcement',
                ])
            </form>
        </div>
    </div>
</div>
@endsection