@extends('portal.layouts.app')

@section('title', 'Edit Exchange Product')

@section('content')
    <div class="ep-form-page">
        <div class="ep-form-header">
            <div>
                <h1 class="ep-form-title">
                    Edit Exchange Product
                </h1>

                <p class="ep-form-subtitle">
                    Update the product information, category,
                    exchange amount, or product image.
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
                'portal.exchange-prizes.update',
                $exchangePrize
            ) }}"
            enctype="multipart/form-data"
        >
            @csrf
            @method('PUT')

            @include(
                'portal.exchange-prizes._form',
                [
                    'submitButtonText' =>
                        'Update Product',
                ]
            )
        </form>
    </div>
@endsection