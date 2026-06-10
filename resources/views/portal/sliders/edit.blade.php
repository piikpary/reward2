@extends('portal.layouts.app')

@section('content')
    <h1 class="page-title">Edit Slider</h1>
    <p class="page-subtitle">Update mobile homepage banner.</p>

    <div class="card profile-card">
        <form method="POST" action="{{ route('portal.sliders.update', $slider) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card-body">
                @if ($slider->image)
                    <div style="margin-bottom: 22px;">
                        <img src="{{ asset('storage/' . $slider->image) }}" style="width: 260px; height: 130px; object-fit: cover; border-radius: 16px; border: 1px solid #eee;">
                    </div>
                @endif

                <div class="form-row">
                    <label class="form-label">
                        <span class="icon">▣</span>
                        <span>Title</span>
                    </label>

                    <div class="field-wrap">
                        <input type="text" name="title" class="form-control" value="{{ old('title', $slider->title) }}" placeholder="Enter title">
                        @error('title') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label">
                        <span class="icon">▧</span>
                        <span>Image</span>
                    </label>

                    <div class="field-wrap">
                        <input type="file" name="image" class="form-control" accept="image/*">
                        @error('image') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label">
                        <span class="icon">↗</span>
                        <span>Link</span>
                    </label>

                    <div class="field-wrap">
                        <input type="text" name="link" class="form-control" value="{{ old('link', $slider->link) }}" placeholder="Optional link">
                        @error('link') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label">
                        <span class="icon">#</span>
                        <span>Sort Order</span>
                    </label>

                    <div class="field-wrap">
                        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $slider->sort_order) }}" min="0">
                        @error('sort_order') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label">
                        <span class="icon">●</span>
                        <span>Status</span>
                    </label>

                    <div class="field-wrap">
                        <select name="status" class="form-control" required>
                            <option value="1" {{ old('status', $slider->status) == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status', $slider->status) == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="save-btn">Update Slider</button>
                <a href="{{ route('portal.sliders.index') }}" class="quick-btn secondary" style="margin-left: 8px;">Cancel</a>
            </div>
        </form>
    </div>
@endsection