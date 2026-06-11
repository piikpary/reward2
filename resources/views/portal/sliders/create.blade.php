@extends('portal.layouts.app')

@section('content')
    <div class="dashboard-header">
        <div>
            <h1 class="page-title">Add Slider</h1>
            <p class="page-subtitle">Create slider campaign with multiple images.</p>
        </div>

        <a href="{{ route('portal.sliders.index') }}" class="quick-btn secondary">
            Back
        </a>
    </div>

    <form method="POST" action="{{ route('portal.sliders.store') }}" enctype="multipart/form-data">
        @csrf
        @include('portal.sliders._form')
    </form>
@endsection