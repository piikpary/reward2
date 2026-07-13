@extends('portal.layouts.app')
@push('styles')
    @include('portal.share-campaigns._styles')
@endpush
@section('title', 'Edit Share Campaign')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">
                Edit Share Campaign
            </h1>

            <p class="page-description">
                Update:
                {{ $shareCampaign->title }}
            </p>
        </div>

        <a
            class="button button-primary"
            href="{{ route(
                'portal.share-campaigns.shares',
                $shareCampaign
            ) }}"
        >
            View Customer Shares
        </a>
    </div>

    <form
        method="POST"
        action="{{ route(
            'portal.share-campaigns.update',
            $shareCampaign
        ) }}"
        enctype="multipart/form-data"
    >
        @csrf
        @method('PUT')

        @include(
            'portal.share-campaigns._form'
        )
    </form>
@endsection