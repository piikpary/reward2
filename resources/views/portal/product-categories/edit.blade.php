@extends('portal.layouts.app')

@section('title', 'Edit Product Category')

@section('content')
    <div
        class="campaign-card"
        style="max-width: 900px;"
    >
        <h1 style="margin: 0 0 8px;">
            Edit Product Category
        </h1>

        <p
            class="muted"
            style="margin: 0 0 24px;"
        >
            Update category information.
        </p>

        <form
            method="POST"
            action="{{ route(
                'portal.product-categories.update',
                $productCategory
            ) }}"
        >
            @csrf
            @method('PUT')

            @include(
                'portal.product-categories._form',
                [
                    'buttonText' =>
                        'Update Category',
                ]
            )
        </form>
    </div>
@endsection