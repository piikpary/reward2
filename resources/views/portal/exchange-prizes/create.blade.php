@extends('portal.layouts.app')

@section('title', 'Create Exchange Product')

@section('content')
    <div class="ep-form-page">
        <div class="ep-form-header">
            <div>
                <h1 class="ep-form-title">
                    Create Exchange Product
                </h1>

                <p class="ep-form-subtitle">
                    Add a new product that users can exchange
                    using their Discount balance.
                </p>
            </div>

            <a
                href="{{ route(
                    'portal.exchange-prizes.index'
                ) }}"
                class="ep-back-button"
            >
                ← Back to Products
            </a>
        </div>

        <form
            method="POST"
            action="{{ route(
                'portal.exchange-prizes.store'
            ) }}"
            enctype="multipart/form-data"
        >
            @csrf

            @include(
                'portal.exchange-prizes._form',
                [
                    'submitButtonText' =>
                        'Create Product',
                ]
            )
        </form>
    </div>
@endsection