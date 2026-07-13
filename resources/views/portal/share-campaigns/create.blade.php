@extends('portal.layouts.app')
@push('styles')
    @include('portal.share-campaigns._styles')
@endpush
@section('title', 'Create Share Campaign')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">
                Create Share Campaign
            </h1>

            <p class="page-description">
                Add a campaign poster and configure
                its share reward.
            </p>
        </div>
    </div>

    <form
        method="POST"
        action="{{ route(
            'portal.share-campaigns.store'
        ) }}"
        enctype="multipart/form-data"
    >
        @csrf

        @include(
            'portal.share-campaigns._form'
        )
    </form>
@endsection