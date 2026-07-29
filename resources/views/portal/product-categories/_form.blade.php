@if ($errors->any())
    <div class="error">
        <ul>
            @foreach ($errors->all() as $error)
                <li>
                    {{ $error }}
                </li>
            @endforeach
        </ul>
    </div>
@endif

<div style="margin-bottom: 20px;">
    <label
        for="name"
        style="
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
        "
    >
        Category Name
        <span style="color: #dc2626;">*</span>
    </label>

    <input
        id="name"
        name="name"
        type="text"
        class="form-control"
        maxlength="150"
        value="{{ old(
            'name',
            $productCategory->name ?? ''
        ) }}"
        placeholder="Example: Beer"
        required
    >
</div>

<div style="margin-bottom: 20px;">
    <label
        for="description"
        style="
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
        "
    >
        Category Description
    </label>

    <textarea
        id="description"
        name="description"
        class="form-control"
        rows="5"
        style="
            height: auto;
            min-height: 130px;
            padding-top: 14px;
        "
        placeholder="Example: All beer products"
    >{{ old(
        'description',
        $productCategory->description ?? ''
    ) }}</textarea>
</div>

<div
    style="
        display: flex;
        gap: 10px;
        margin-top: 24px;
    "
>
    <button
        type="submit"
        class="button button-primary"
    >
        {{ $buttonText }}
    </button>

    <a
        href="{{ route(
            'portal.product-categories.index'
        ) }}"
        class="button button-secondary"
    >
        Cancel
    </a>
</div>