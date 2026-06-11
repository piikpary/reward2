<div class="card profile-card">
    <div class="card-body">
        <div class="form-row">
            <label class="form-label">
                <span class="icon">T</span>
                <span>Title</span>
            </label>

            <div class="field-wrap">
                <input
                    type="text"
                    name="title"
                    class="form-control"
                    value="{{ old('title', $slider->title ?? '') }}"
                    placeholder="Example: 20% Discount Campaign"
                    required
                >
                @error('title')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-row">
            <label class="form-label">
                <span class="icon">✎</span>
                <span>Description</span>
            </label>

            <div class="field-wrap">
                <textarea
                    name="description"
                    class="form-control"
                    rows="4"
                    placeholder="Example: Spin now and get up to 20% discount."
                >{{ old('description', $slider->description ?? '') }}</textarea>
                @error('description')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-row">
            <label class="form-label">
                <span class="icon">↗</span>
                <span>Link</span>
            </label>

            <div class="field-wrap">
                <input
                    type="url"
                    name="link"
                    class="form-control"
                    value="{{ old('link', $slider->link ?? '') }}"
                    placeholder="https://example.com/promotion"
                >
                @error('link')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-row">
            <label class="form-label">
                <span class="icon">#</span>
                <span>Sort Order</span>
            </label>

            <div class="field-wrap">
                <input
                    type="number"
                    name="sort_order"
                    class="form-control"
                    value="{{ old('sort_order', $slider->sort_order ?? 0) }}"
                    min="0"
                >
                @error('sort_order')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-row">
            <label class="form-label">
                <span class="icon">●</span>
                <span>Status</span>
            </label>

            <div class="field-wrap">
                <select name="status" class="form-control" required>
                    <option value="active" {{ old('status', $slider->status ?? 'active') === 'active' ? 'selected' : '' }}>
                        Active
                    </option>
                    <option value="inactive" {{ old('status', $slider->status ?? '') === 'inactive' ? 'selected' : '' }}>
                        Inactive
                    </option>
                </select>

                @error('status')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>
        </div>

        @if (isset($slider) && $slider->images->count())
            <div class="form-row">
                <label class="form-label">
                    <span class="icon">🖼</span>
                    <span>Current Images</span>
                </label>

                <div class="field-wrap">
                    <div class="current-images">
                        @foreach ($slider->images as $image)
                            <label class="current-image-card">
                                <img src="{{ asset('storage/' . $image->image) }}" alt="Slider Image">
                                <span>
                                    <input type="checkbox" name="delete_image_ids[]" value="{{ $image->id }}">
                                    Delete
                                </span>
                            </label>
                        @endforeach
                    </div>

                    @error('delete_image_ids')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        @endif

        <div class="form-row">
            <label class="form-label">
                <span class="icon">🖼</span>
                <span>Images</span>
            </label>

            <div class="field-wrap">
                <input
                    type="file"
                    name="images[]"
                    class="form-control"
                    accept="image/*"
                    multiple
                    {{ isset($slider) ? '' : 'required' }}
                >

                <small class="help-text">
                    You can upload multiple images. Supported: JPG, PNG, WEBP.
                </small>

                @error('images')
                    <div class="error">{{ $message }}</div>
                @enderror

                @error('images.*')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="card-footer">
        <button type="submit" class="save-btn">Save Slider</button>
    </div>
</div>

<style>
    .current-images {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .current-image-card {
        width: 150px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 8px;
        background: #fff;
        cursor: pointer;
    }

    .current-image-card img {
        width: 100%;
        height: 86px;
        object-fit: cover;
        border-radius: 10px;
        display: block;
        margin-bottom: 8px;
    }

    .current-image-card span {
        display: flex;
        gap: 6px;
        align-items: center;
        font-size: 13px;
        color: #b91c1c;
        font-weight: 700;
    }

    .help-text {
        display: block;
        margin-top: 7px;
        color: #6b7280;
        font-size: 13px;
    }
</style>