@extends('portal.layouts.app')

@section('content')
    <div class="dashboard-header">
        <div>
            <h1 class="page-title">Edit Discount</h1>
            <p class="page-subtitle">Update discount percentage and status.</p>
        </div>

        <a href="{{ route('portal.discounts.index') }}" class="quick-btn secondary">
            Back
        </a>
    </div>

    <form method="POST" action="{{ route('portal.discounts.update', $discount) }}">
        @csrf
        @method('PUT')
        @include('portal.discounts._form')
    </form>
@endsection