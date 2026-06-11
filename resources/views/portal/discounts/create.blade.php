@extends('portal.layouts.app')

@section('content')
    <div class="dashboard-header">
        <div>
            <h1 class="page-title">Add Discount</h1>
            <p class="page-subtitle">Create a global discount percentage for the mobile app.</p>
        </div>

        <a href="{{ route('portal.discounts.index') }}" class="quick-btn secondary">
            Back
        </a>
    </div>

    <form method="POST" action="{{ route('portal.discounts.store') }}">
        @csrf
        @include('portal.discounts._form')
    </form>
@endsection