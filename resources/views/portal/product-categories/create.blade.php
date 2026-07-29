@extends('portal.layouts.app')

@section('title', 'Create Product Category')

@section('content')
    <div
        class="campaign-card"
        style="max-width: 900px;"
    >
        <h1 style="margin: 0 0 8px;">
            Create Product Category
        </h1>

        <p
            class="muted"
            style="margin: 0 0 24px;"
        >
            Add a category for organizing exchange products.
        </p>

        <form
            method="POST"
            action="{{ route(
                'portal.product-categories.store'
            ) }}"
        >
            @csrf

            @include(
                'portal.product-categories._form',
                [
                    'buttonText' =>
                        'Create Category',
                ]
            )
        </form>
    </div>
@endsection