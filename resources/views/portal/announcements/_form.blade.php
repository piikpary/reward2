@php
    $announcementDateValue = old(
        'announcement_date',
        $announcement->announcement_date
            ? $announcement->announcement_date->format('Y-m-d\TH:i')
            : now()->format('Y-m-d\TH:i')
    );

    $currentStatus = old(
        'status',
        $announcement->status ?? 'active'
    );

    $buttonText = $buttonText ?? 'Save Announcement';
@endphp

@csrf

@if($errors->any())
    <div class="form-alert error">
        <strong>Please correct the following information:</strong>

        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="form-grid">
    <div class="form-group full">
        <label for="title">
            Announcement Title
            <span class="required">*</span>
        </label>

        <input
            id="title"
            type="text"
            name="title"
            class="form-control"
            value="{{ old('title', $announcement->title) }}"
            placeholder="Example: New Lucky Draw Campaign"
            maxlength="255"
            required
        >

        @error('title')
            <small class="error-text">
                {{ $message }}
            </small>
        @enderror
    </div>

    <div class="form-group">
        <label for="announcement_date">
            Announcement Date
            <span class="required">*</span>
        </label>

        <input
            id="announcement_date"
            type="datetime-local"
            name="announcement_date"
            class="form-control"
            value="{{ $announcementDateValue }}"
            required
        >

        <small class="help-text">
            The announcement becomes available after this date and time.
        </small>

        @error('announcement_date')
            <small class="error-text">
                {{ $message }}
            </small>
        @enderror
    </div>

    <div class="form-group">
        <label for="status">
            Status
            <span class="required">*</span>
        </label>

        <select
            id="status"
            name="status"
            class="form-control"
            required
        >
            <option
                value="active"
                @selected($currentStatus === 'active')
            >
                Active
            </option>

            <option
                value="inactive"
                @selected($currentStatus === 'inactive')
            >
                Inactive
            </option>
        </select>

        <small class="help-text">
            Only active and published announcements appear in the API.
        </small>

        @error('status')
            <small class="error-text">
                {{ $message }}
            </small>
        @enderror
    </div>

    <div class="form-group full">
        <label for="content">
            Content
            <span class="required">*</span>
        </label>

        <textarea
            id="content"
            name="content"
            class="form-control"
            placeholder="Enter announcement content"
            required
        >{{ old('content', $announcement->content) }}</textarea>

        @error('content')
            <small class="error-text">
                {{ $message }}
            </small>
        @enderror
    </div>

    <div class="form-group full">
        <label for="images">
            Announcement Images

            @if(!$announcement->exists)
                <span class="required">*</span>
            @endif
        </label>

        <input
            id="images"
            type="file"
            name="images[]"
            class="form-control"
            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
            multiple
            @required(!$announcement->exists)
        >

        <small class="help-text">
            Select up to 10 images. Maximum 5 MB per image.
            Supported: JPG, JPEG, PNG, WEBP.
        </small>

        @error('images')
            <small class="error-text">
                {{ $message }}
            </small>
        @enderror

        @error('images.*')
            <small class="error-text">
                {{ $message }}
            </small>
        @enderror

        <div
            id="new-image-preview"
            class="image-preview-grid"
        ></div>
    </div>

    @if(
        $announcement->exists
        && $announcement->images->isNotEmpty()
    )
        <div class="form-group full">
            <label>Current Images</label>

            <div class="image-preview-grid">
                @foreach($announcement->images as $image)
                    <div class="image-preview-item">
                        <img
                            src="{{ $image->image_url }}"
                            alt="{{ $announcement->title }}"
                        >

                        <div class="image-preview-footer">
                            <button
                                type="submit"
                                form="delete-image-{{ $image->id }}"
                                class="btn btn-sm btn-danger"
                                onclick="return confirm(
                                    'Delete this image?'
                                )"
                            >
                                Delete Image
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<div class="form-actions">
    <a
        href="{{ route('portal.announcements.index') }}"
        class="btn btn-light"
    >
        Cancel
    </a>

    <button
        type="submit"
        class="btn btn-dark"
    >
        {{ $buttonText }}
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const imageInput =
            document.getElementById('images');

        const previewContainer =
            document.getElementById('new-image-preview');

        if (!imageInput || !previewContainer) {
            return;
        }

        imageInput.addEventListener('change', function () {
            previewContainer.innerHTML = '';

            Array.from(this.files).forEach(function (file) {
                if (!file.type.startsWith('image/')) {
                    return;
                }

                const reader = new FileReader();

                reader.onload = function (event) {
                    const item =
                        document.createElement('div');

                    item.className = 'image-preview-item';

                    const image =
                        document.createElement('img');

                    image.src = event.target.result;
                    image.alt = file.name;

                    item.appendChild(image);
                    previewContainer.appendChild(item);
                };

                reader.readAsDataURL(file);
            });
        });
    });
</script>