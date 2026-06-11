@extends('portal.layouts.app')

@section('content')
    <div class="dashboard-header">
        <div>
            <h1 class="page-title">Edit Slider</h1>
            <p class="page-subtitle">Update slider campaign and images.</p>
        </div>

        <a href="{{ route('portal.sliders.index') }}" class="quick-btn secondary">
            Back
        </a>
    </div>

    <form method="POST" action="{{ route('portal.sliders.update', $slider) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('portal.sliders._form')
    </form>
@endsection