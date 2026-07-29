@php
    $isEdit =
        isset($exchangePrize)
        && $exchangePrize
        && $exchangePrize->exists;
@endphp

@once
    @push('styles')
        <style>
            .ep-form-page {
                width: 100%;
                max-width: 1100px;
            }

            .ep-form-header {
                margin-bottom: 24px;
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 20px;
            }

            .ep-form-title {
                margin: 0;
                color: #0f172a;
                font-size: 30px;
                font-weight: 800;
                line-height: 1.2;
                letter-spacing: -0.04em;
            }

            .ep-form-subtitle {
                max-width: 700px;
                margin: 8px 0 0;
                color: #64748b;
                font-size: 14px;
                line-height: 1.7;
            }

            .ep-back-button {
                min-height: 42px;
                padding: 0 16px;
                border: 1px solid #cbd5e1;
                border-radius: 11px;
                background: #ffffff;
                color: #334155;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 7px;
                text-decoration: none;
                font-size: 13px;
                font-weight: 700;
                white-space: nowrap;
            }

            .ep-back-button:hover {
                border-color: #0d1b2a;
                color: #0d1b2a;
            }

            .ep-form-alert {
                margin-bottom: 20px;
                padding: 14px 16px;
                border: 1px solid #fecaca;
                border-radius: 12px;
                background: #fef2f2;
                color: #b91c1c;
                font-size: 14px;
            }

            .ep-form-alert ul {
                margin: 8px 0 0;
                padding-left: 20px;
            }

            .ep-form-card {
                overflow: hidden;
                border: 1px solid #e5e7eb;
                border-radius: 18px;
                background: #ffffff;
                box-shadow:
                    0 10px 30px
                    rgba(15, 23, 42, 0.06);
            }

            .ep-form-card-header {
                padding: 20px 24px;
                border-bottom: 1px solid #e5e7eb;
                background: #f8fafc;
            }

            .ep-form-card-title {
                margin: 0;
                color: #0f172a;
                font-size: 17px;
                font-weight: 800;
            }

            .ep-form-card-note {
                margin: 5px 0 0;
                color: #64748b;
                font-size: 13px;
            }

            .ep-form-body {
                padding: 26px 24px;
            }

            .ep-form-grid {
                display: grid;
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
                gap: 22px;
            }

            .ep-field {
                min-width: 0;
            }

            .ep-field-full {
                grid-column: 1 / -1;
            }

            .ep-label {
                margin-bottom: 8px;
                color: #0f172a;
                display: flex;
                align-items: center;
                gap: 6px;
                font-size: 13px;
                font-weight: 700;
            }

            .ep-required {
                color: #dc2626;
            }

            .ep-input,
            .ep-select {
                width: 100%;
                height: 48px;
                padding: 0 14px;
                border: 1px solid #dbe2ea;
                border-radius: 12px;
                background: #ffffff;
                color: #0f172a;
                outline: none;
                font-size: 14px;
                transition: 0.18s ease;
            }

            .ep-input:focus,
            .ep-select:focus {
                border-color: #0d1b2a;
                box-shadow:
                    0 0 0 4px
                    rgba(13, 27, 42, 0.1);
            }

            .ep-field-help {
                margin-top: 7px;
                color: #94a3b8;
                font-size: 11px;
                line-height: 1.5;
            }

            .ep-field-error {
                margin-top: 7px;
                color: #dc2626;
                font-size: 12px;
                font-weight: 600;
            }

            .ep-image-area {
                padding: 20px;
                border: 1px dashed #cbd5e1;
                border-radius: 16px;
                background: #f8fafc;
            }

            .ep-image-grid {
                display: grid;
                grid-template-columns:
                    170px minmax(0, 1fr);
                gap: 20px;
                align-items: center;
            }

            .ep-image-preview {
                width: 170px;
                height: 150px;
                border: 1px solid #e5e7eb;
                border-radius: 14px;
                background: #ffffff;
                object-fit: cover;
            }

            .ep-image-placeholder {
                width: 170px;
                height: 150px;
                border: 1px solid #e5e7eb;
                border-radius: 14px;
                background: #ffffff;
                color: #94a3b8;
                display: grid;
                place-items: center;
                font-size: 34px;
            }

            .ep-file-input {
                width: 100%;
                padding: 12px;
                border: 1px solid #dbe2ea;
                border-radius: 12px;
                background: #ffffff;
                color: #334155;
                font-size: 13px;
            }

            .ep-form-footer {
                padding: 20px 24px;
                border-top: 1px solid #e5e7eb;
                background: #fafafa;
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: 10px;
                flex-wrap: wrap;
            }

            .ep-cancel-button,
            .ep-save-button {
                min-height: 44px;
                padding: 0 18px;
                border-radius: 11px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                font-size: 13px;
                font-weight: 700;
                text-decoration: none;
                cursor: pointer;
            }

            .ep-cancel-button {
                border: 1px solid #cbd5e1;
                background: #ffffff;
                color: #334155;
            }

            .ep-cancel-button:hover {
                border-color: #0d1b2a;
                color: #0d1b2a;
            }

            .ep-save-button {
                border: none;
                background: #0d1b2a;
                color: #ffffff;
                box-shadow:
                    0 7px 18px
                    rgba(13, 27, 42, 0.16);
            }

            .ep-save-button:hover {
                background: #08111d;
            }

            @media (max-width: 800px) {
                .ep-form-header {
                    flex-direction: column;
                }

                .ep-form-grid {
                    grid-template-columns: 1fr;
                }

                .ep-field-full {
                    grid-column: auto;
                }

                .ep-image-grid {
                    grid-template-columns: 1fr;
                }

                .ep-form-footer {
                    align-items: stretch;
                    flex-direction: column-reverse;
                }

                .ep-cancel-button,
                .ep-save-button {
                    width: 100%;
                }
            }
        </style>
    @endpush
@endonce

@if ($errors->any())
    <div class="ep-form-alert">
        <strong>
            Please correct the following errors:
        </strong>

        <ul>
            @foreach ($errors->all() as $error)
                <li>
                    {{ $error }}
                </li>
            @endforeach
        </ul>
    </div>
@endif

<div class="ep-form-card">
    <div class="ep-form-card-header">
        <h2 class="ep-form-card-title">
            Product Information
        </h2>

        <p class="ep-form-card-note">
            Enter the exchange product details below.
        </p>
    </div>

    <div class="ep-form-body">
        <div class="ep-form-grid">
            <div class="ep-field">
                <label
                    for="title"
                    class="ep-label"
                >
                    Product Title
                    <span class="ep-required">*</span>
                </label>

                <input
                    id="title"
                    name="title"
                    type="text"
                    class="ep-input"
                    maxlength="255"
                    value="{{ old(
                        'title',
                        $exchangePrize->title ?? ''
                    ) }}"
                    placeholder="Example: Premium Lager Beer"
                    required
                >

                @error('title')
                    <div class="ep-field-error">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="ep-field">
                <label
                    for="product_category_id"
                    class="ep-label"
                >
                    Product Category
                    <span class="ep-required">*</span>
                </label>

                <select
                    id="product_category_id"
                    name="product_category_id"
                    class="ep-select"
                    required
                >
                    <option value="">
                        Select Product Category
                    </option>

                    @foreach ($categories as $category)
                        <option
                            value="{{ $category->id }}"
                            @selected(
                                (string) old(
                                    'product_category_id',
                                    $exchangePrize
                                        ->product_category_id
                                    ?? ''
                                )
                                === (string) $category->id
                            )
                        >
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

                @error('product_category_id')
                    <div class="ep-field-error">
                        {{ $message }}
                    </div>
                @enderror

                @if ($categories->isEmpty())
                    <div class="ep-field-error">
                        No categories found. Please create a
                        product category first.
                    </div>
                @endif
            </div>

            <div class="ep-field">
                    <label
                        for="exchange_discount_amount"
                        class="ep-label"
                    >
                        Exchange Discount Amount
                        <span class="ep-required">*</span>
                    </label>

                    <input
                        id="exchange_discount_amount"
                        name="exchange_discount_amount"
                        type="number"
                        class="ep-input"
                        min="0"
                        step="0.01"
                        value="{{ old(
                            'exchange_discount_amount',
                            $exchangePrize->exchange_discount_amount ?? ''
                        ) }}"
                        placeholder="Example: 25"
                        required
                    >

                    <div class="ep-field-help">
                        Amount of Discount balance required
                        to exchange this product.
                    </div>

                    @error('exchange_discount_amount')
                        <div class="ep-field-error">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="ep-field">
                    <label
                        for="unit"
                        class="ep-label"
                    >
                        Unit
                        <span class="ep-required">*</span>
                    </label>

                    <input
                        id="unit"
                        name="unit"
                        type="text"
                        class="ep-input"
                        maxlength="100"
                        value="{{ old(
                            'unit',
                            $exchangePrize->unit ?? ''
                        ) }}"
                        placeholder="Example: កំប៉ុង, ដប, ប្រអប់"
                        required
                    >

                    <div class="ep-field-help">
                        Example: កំប៉ុង, ដប, ប្រអប់.
                    </div>

                    @error('unit')
                        <div class="ep-field-error">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

            <div class="ep-field ep-field-full">
                <label
                    for="image"
                    class="ep-label"
                >
                    Product Image

                    @unless ($isEdit)
                        <span class="ep-required">*</span>
                    @endunless
                </label>

                <div class="ep-image-area">
                    <div class="ep-image-grid">
                        <div>
                            @if (
                                $isEdit
                                && $exchangePrize->imageUrl()
                            )
                                <img
                                    id="ep-image-preview"
                                    src="{{ $exchangePrize
                                        ->imageUrl() }}"
                                    alt="{{ $exchangePrize->title }}"
                                    class="ep-image-preview"
                                >
                            @else
                                <div
                                    id="ep-image-placeholder"
                                    class="ep-image-placeholder"
                                >
                                    🎁
                                </div>

                                <img
                                    id="ep-image-preview"
                                    src=""
                                    alt="Product preview"
                                    class="ep-image-preview"
                                    style="display: none;"
                                >
                            @endif
                        </div>

                        <div>
                            <input
                                id="image"
                                name="image"
                                type="file"
                                class="ep-file-input"
                                accept=".jpg,.jpeg,.png,.webp"
                                @unless($isEdit)
                                    required
                                @endunless
                            >

                            <div class="ep-field-help">
                                Allowed formats: JPG, JPEG,
                                PNG, and WEBP. Maximum size:
                                5 MB.
                            </div>

                            @if ($isEdit)
                                <div class="ep-field-help">
                                    Leave empty to keep the
                                    current product image.
                                </div>
                            @endif

                            @error('image')
                                <div class="ep-field-error">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="ep-form-footer">
        <a
            href="{{ route(
                'portal.exchange-prizes.index'
            ) }}"
            class="ep-cancel-button"
        >
            Cancel
        </a>

        <button
            type="submit"
            class="ep-save-button"
            @disabled($categories->isEmpty())
        >
            <span>✓</span>

            <span>
                {{ $submitButtonText }}
            </span>
        </button>
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener(
                'DOMContentLoaded',
                function () {
                    const imageInput =
                        document.getElementById('image');

                    const imagePreview =
                        document.getElementById(
                            'ep-image-preview'
                        );

                    const imagePlaceholder =
                        document.getElementById(
                            'ep-image-placeholder'
                        );

                    if (
                        !imageInput
                        || !imagePreview
                    ) {
                        return;
                    }

                    imageInput.addEventListener(
                        'change',
                        function (event) {
                            const file =
                                event.target.files[0];

                            if (!file) {
                                return;
                            }

                            const reader =
                                new FileReader();

                            reader.onload =
                                function (readerEvent) {
                                    imagePreview.src =
                                        readerEvent
                                            .target
                                            .result;

                                    imagePreview.style
                                        .display =
                                            'block';

                                    if (
                                        imagePlaceholder
                                    ) {
                                        imagePlaceholder
                                            .style
                                            .display =
                                                'none';
                                    }
                                };

                            reader.readAsDataURL(file);
                        }
                    );
                }
            );
        </script>
    @endpush
@endonce