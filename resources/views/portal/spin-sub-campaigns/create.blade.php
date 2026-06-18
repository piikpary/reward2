@extends('portal.layouts.app')

@section('content')
<style>
    .sub-page {
        padding: 8px 0 35px;
    }

    .sub-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        margin-bottom: 24px;
    }

    .sub-title {
        margin: 0;
        color: #071629;
        font-size: 32px;
        font-weight: 900;
        letter-spacing: -0.7px;
    }

    .sub-description {
        margin: 7px 0 0;
        color: #64748b;
        font-size: 15px;
    }

    .back-btn {
        min-height: 44px;
        padding: 0 18px;
        border: 1px solid #dfe5ec;
        border-radius: 12px;
        background: #ffffff;
        color: #0b1b2b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 800;
        transition: 0.2s ease;
    }

    .back-btn:hover {
        background: #f8fafc;
        transform: translateY(-1px);
    }

    .campaign-summary {
        display: grid;
        grid-template-columns: 1.4fr 1fr 1fr;
        gap: 16px;
        margin-bottom: 22px;
    }

    .summary-card {
        padding: 20px;
        border: 1px solid #e5eaf0;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
    }

    .summary-card span {
        display: block;
        margin-bottom: 8px;
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .summary-card strong {
        color: #071629;
        font-size: 18px;
        font-weight: 900;
    }

    .form-card {
        overflow: hidden;
        border: 1px solid #e5eaf0;
        border-radius: 22px;
        background: #ffffff;
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.07);
    }

    .form-card-header {
        padding: 24px 28px;
        border-bottom: 1px solid #edf1f5;
        background: linear-gradient(135deg, #f8fafc, #ffffff);
    }

    .form-card-header h2 {
        margin: 0;
        color: #071629;
        font-size: 21px;
        font-weight: 900;
    }

    .form-card-header p {
        margin: 7px 0 0;
        color: #64748b;
        font-size: 14px;
    }

    .form-body {
        padding: 28px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px;
    }

    .form-group {
        min-width: 0;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-label {
        display: block;
        margin-bottom: 9px;
        color: #172033;
        font-size: 14px;
        font-weight: 850;
    }

    .required {
        color: #dc2626;
    }

    .form-control {
        width: 100%;
        height: 50px;
        padding: 0 15px;
        border: 1px solid #d8e0e8;
        border-radius: 12px;
        background: #ffffff;
        color: #071629;
        font-size: 14px;
        outline: none;
        box-sizing: border-box;
        transition: 0.2s ease;
    }

    textarea.form-control {
        min-height: 120px;
        height: auto;
        padding-top: 14px;
        resize: vertical;
        line-height: 1.6;
    }

    .form-control:focus {
        border-color: #0b1b2b;
        box-shadow: 0 0 0 4px rgba(11, 27, 43, 0.08);
    }

    .input-wrap {
        position: relative;
    }

    .input-wrap .form-control {
        padding-right: 48px;
    }

    .input-suffix {
        position: absolute;
        top: 50%;
        right: 16px;
        color: #64748b;
        font-size: 14px;
        font-weight: 800;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .helper-text {
        margin: 7px 0 0;
        color: #8491a3;
        font-size: 12px;
        line-height: 1.5;
    }

    .error-text {
        display: block;
        margin-top: 7px;
        color: #dc2626;
        font-size: 12px;
        font-weight: 700;
    }

    .validation-box {
        margin-bottom: 22px;
        padding: 15px 17px;
        border: 1px solid #fecaca;
        border-radius: 12px;
        background: #fef2f2;
        color: #991b1b;
        font-size: 14px;
    }

    .validation-box ul {
        margin: 8px 0 0;
        padding-left: 20px;
    }

    .special-spin-box {
        grid-column: 1 / -1;
        padding: 20px;
        border: 1px solid #ddd6fe;
        border-radius: 16px;
        background: #faf8ff;
    }
    .special-discount-list {
    margin-top: 18px;
    display: flex;
    flex-direction: column;
    gap: 13px;
}

.special-discount-row {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 12px;
    align-items: end;
}

.special-discount-field {
    min-width: 0;
}

.add-special-btn,
.remove-special-btn {
    min-height: 46px;
    padding: 0 17px;
    border-radius: 11px;
    font-size: 13px;
    font-weight: 850;
    cursor: pointer;
    transition: 0.2s ease;
}

.add-special-btn {
    margin-top: 15px;
    border: 1px solid #6d28d9;
    background: #6d28d9;
    color: #ffffff;
}

.remove-special-btn {
    border: 1px solid #fecaca;
    background: #fef2f2;
    color: #dc2626;
}

.add-special-btn:hover,
.remove-special-btn:hover {
    transform: translateY(-1px);
}

.add-special-btn:disabled,
.remove-special-btn:disabled {
    cursor: not-allowed;
    opacity: 0.5;
    transform: none;
}

.special-spin-disabled {
    opacity: 0.65;
}

@media (max-width: 700px) {
    .special-discount-row {
        grid-template-columns: 1fr;
    }

    .remove-special-btn {
        width: 100%;
    }
}

    .special-spin-heading {
        margin: 0 0 16px;
        color: #4c1d95;
        font-size: 16px;
        font-weight: 900;
    }

    .special-spin-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px;
    }

    .checkbox-label {
        min-height: 50px;
        padding: 0 15px;
        border: 1px solid #d8e0e8;
        border-radius: 12px;
        background: #ffffff;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #172033;
        font-size: 14px;
        font-weight: 800;
        cursor: pointer;
    }

    .checkbox-label input {
        width: 18px;
        height: 18px;
        margin: 0;
        cursor: pointer;
    }

    .form-footer {
        padding: 20px 28px;
        border-top: 1px solid #edf1f5;
        background: #fafbfc;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 12px;
    }

    .cancel-btn,
    .save-btn {
        min-height: 46px;
        padding: 0 21px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 850;
        text-decoration: none;
        cursor: pointer;
        transition: 0.2s ease;
    }

    .cancel-btn {
        border: 1px solid #dfe5ec;
        background: #ffffff;
        color: #172033;
    }

    .save-btn {
        border: 1px solid #0b1b2b;
        background: #0b1b2b;
        color: #ffffff;
    }

    .cancel-btn:hover,
    .save-btn:hover {
        transform: translateY(-1px);
    }

    @media (max-width: 900px) {
        .campaign-summary,
        .form-grid,
        .special-spin-grid {
            grid-template-columns: 1fr;
        }

        .form-group.full-width {
            grid-column: auto;
        }
    }

    @media (max-width: 640px) {
        .sub-header {
            flex-direction: column;
        }

        .sub-title {
            font-size: 27px;
        }

        .form-body,
        .form-card-header,
        .form-footer {
            padding-left: 18px;
            padding-right: 18px;
        }

        .form-footer {
            flex-direction: column-reverse;
        }

        .cancel-btn,
        .save-btn {
            width: 100%;
        }
    }
</style>

@php
    $specialDiscountValues = old(
        'special_discounts',
        ['']
    );

    if (
        !is_array($specialDiscountValues)
        || empty($specialDiscountValues)
    ) {
        $specialDiscountValues = [''];
    }

    $specialDiscountEnabled = (bool) old(
        'special_discount_enabled',
        false
    );
@endphp

<div class="sub-page">
    <div class="sub-header">
        <div>
            <h1 class="sub-title">Add Subcampaign</h1>

            <p class="sub-description">
                Create another case and spin rule inside the same main campaign period.
            </p>
        </div>

        <a
            href="{{ route('portal.spin-campaigns.sub-campaigns.index', $campaign) }}"
            class="back-btn"
        >
            ← Back to Subcampaigns
        </a>
    </div>

    <div class="campaign-summary">
        <div class="summary-card">
            <span>Main Campaign</span>
            <strong>{{ $campaign->name }}</strong>
        </div>

        <div class="summary-card">
            <span>Start Date</span>

            <strong>
                {{ \Carbon\Carbon::parse($campaign->start_date)->format('d M Y') }}
            </strong>
        </div>

        <div class="summary-card">
            <span>End Date</span>

            <strong>
                {{ \Carbon\Carbon::parse($campaign->end_date)->format('d M Y') }}
            </strong>
        </div>
    </div>

    @if($errors->any())
        <div class="validation-box">
            <strong>Please correct the following information:</strong>

            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        method="POST"
        action="{{ route(
            'portal.spin-campaigns.sub-campaigns.store',
            $campaign
        ) }}"
        class="form-card"
    >
        @csrf

        <div class="form-card-header">
            <h2>Subcampaign Rule</h2>

            <p>
                Each subcampaign uses the main campaign period but has its own
                total cases, spins per case, and discount total.
            </p>
        </div>

        <div class="form-body">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label class="form-label" for="name">
                        Subcampaign Name
                        <span class="required">*</span>
                    </label>

                    <input
                        id="name"
                        type="text"
                        name="name"
                        class="form-control"
                        value="{{ old('name') }}"
                        placeholder="Example: Standard Case Rule"
                        required
                    >

                    @error('name')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="total_cases">
                        Total Cases
                        <span class="required">*</span>
                    </label>

                    <input
                        id="total_cases"
                        type="number"
                        name="total_cases"
                        class="form-control"
                        value="{{ old('total_cases', 1000) }}"
                        min="1"
                        required
                    >

                    <p class="helper-text">
                        Number of cases available for this subcampaign.
                    </p>

                    @error('total_cases')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="spins_per_case">
                        Spins Per Case
                        <span class="required">*</span>
                    </label>

                    <input
                        id="spins_per_case"
                        type="number"
                        name="spins_per_case"
                        class="form-control"
                        value="{{ old('spins_per_case', 4) }}"
                        min="1"
                        required
                    >

                    <p class="helper-text">
                        Example: 4 means every case contains four spins.
                    </p>

                    @error('spins_per_case')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="normal_discount_total">
                        Normal Discount Total Per Case
                        <span class="required">*</span>
                    </label>

                    <div class="input-wrap">
                        <input
                            id="normal_discount_total"
                            type="number"
                            step="0.01"
                            name="normal_discount_total"
                            class="form-control"
                            value="{{ old('normal_discount_total', 30) }}"
                            min="0"
                            required
                        >

                        <span class="input-suffix">%</span>
                    </div>

                    <p class="helper-text">
                        All spins in one normal case must add up to this value.
                    </p>

                    @error('normal_discount_total')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="priority">
                        Priority
                        <span class="required">*</span>
                    </label>

                    <input
                        id="priority"
                        type="number"
                        name="priority"
                        class="form-control"
                        value="{{ old('priority', 1) }}"
                        min="1"
                        required
                    >

                    <p class="helper-text">
                        Higher priority subcampaigns can be selected first.
                    </p>

                    @error('priority')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="status">
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
                            @selected(
                                old('status', 'active') === 'active'
                            )
                        >
                            Active
                        </option>

                        <option
                            value="inactive"
                            @selected(
                                old('status') === 'inactive'
                            )
                        >
                            Inactive
                        </option>
                    </select>

                    @error('status')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div
    id="special-spin-box"
    class="special-spin-box"
>
    <h3 class="special-spin-heading">
        Special Spin Discounts
    </h3>

    <div class="form-group">
        <input
            type="hidden"
            name="special_discount_enabled"
            value="0"
        >

        <label
            for="special_discount_enabled"
            class="checkbox-label"
        >
            <input
                id="special_discount_enabled"
                type="checkbox"
                name="special_discount_enabled"
                value="1"
                @checked($specialDiscountEnabled)
            >

            Enable Special Spin Discounts
        </label>

        <p class="helper-text">
            Every special discount receives its own hidden random
            position inside the existing subcampaign spin quota.
            Adding rewards does not increase the total number of spins.
        </p>

        @error('special_discount_enabled')
            <span class="error-text">
                {{ $message }}
            </span>
        @enderror
    </div>

    <div
        id="special-discount-list"
        class="special-discount-list"
    >
        @foreach($specialDiscountValues as $index => $discountValue)
            <div class="special-discount-row">
                <div class="special-discount-field">
                    <label class="form-label">
                        Special Spin Discount (%)
                        <span class="required">*</span>
                    </label>

                    <div class="input-wrap">
                        <input
                            type="number"
                            name="special_discounts[]"
                            class="form-control special-discount-input"
                            value="{{ $discountValue }}"
                            min="0.01"
                            step="0.01"
                            placeholder="Example: 100"
                        >

                        <span class="input-suffix">
                            %
                        </span>
                    </div>
                </div>

                <button
                    type="button"
                    class="remove-special-btn"
                >
                    Remove
                </button>
            </div>
        @endforeach
    </div>

    <button
        type="button"
        id="add-special-discount"
        class="add-special-btn"
    >
        + Add Special Discount
    </button>

    <p class="helper-text">
        Each row creates one separate special spin reward,
        one hidden position, and one unique verification code.
    </p>

    @error('special_discounts')
        <span class="error-text">
            {{ $message }}
        </span>
    @enderror

    @error('special_discounts.*')
        <span class="error-text">
            {{ $message }}
        </span>
    @enderror
</div>

                <div class="form-group full-width">
                    <label class="form-label" for="description">
                        Description
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        class="form-control"
                        placeholder="Add optional information about this subcampaign rule."
                    >{{ old('description') }}</textarea>

                    @error('description')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-footer">
            <a
                href="{{ route(
                    'portal.spin-campaigns.sub-campaigns.index',
                    $campaign
                ) }}"
                class="cancel-btn"
            >
                Cancel
            </a>

            <button
                type="submit"
                class="save-btn"
            >
                Save Subcampaign
            </button>
        </div>
    </form>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {
    const enabledCheckbox = document.getElementById(
        'special_discount_enabled'
    );

    const specialSpinBox = document.getElementById(
        'special-spin-box'
    );

    const list = document.getElementById(
        'special-discount-list'
    );

    const addButton = document.getElementById(
        'add-special-discount'
    );

    function createSpecialDiscountRow() {
        const row = document.createElement('div');

        row.className = 'special-discount-row';

        row.innerHTML = `
            <div class="special-discount-field">
                <label class="form-label">
                    Special Spin Discount (%)
                    <span class="required">*</span>
                </label>

                <div class="input-wrap">
                    <input
                        type="number"
                        name="special_discounts[]"
                        class="form-control special-discount-input"
                        min="0.01"
                        step="0.01"
                        placeholder="Example: 100"
                    >

                    <span class="input-suffix">%</span>
                </div>
            </div>

            <button
                type="button"
                class="remove-special-btn"
            >
                Remove
            </button>
        `;

        return row;
    }

    function updateSpecialDiscountState() {
        const enabled = enabledCheckbox.checked;

        specialSpinBox.classList.toggle(
            'special-spin-disabled',
            !enabled
        );

        list.querySelectorAll(
            '.special-discount-input'
        ).forEach(function (input) {
            input.disabled = !enabled;
            input.required = enabled;
        });

        list.querySelectorAll(
            '.remove-special-btn'
        ).forEach(function (button) {
            button.disabled = !enabled;
        });

        addButton.disabled = !enabled;
    }

    addButton.addEventListener('click', function () {
        list.appendChild(
            createSpecialDiscountRow()
        );

        updateSpecialDiscountState();
    });

    list.addEventListener('click', function (event) {
        if (
            !event.target.classList.contains(
                'remove-special-btn'
            )
        ) {
            return;
        }

        const rows = list.querySelectorAll(
            '.special-discount-row'
        );

        const currentRow = event.target.closest(
            '.special-discount-row'
        );

        if (rows.length > 1) {
            currentRow.remove();
        } else {
            const input = currentRow.querySelector(
                '.special-discount-input'
            );

            input.value = '';
        }
    });

    enabledCheckbox.addEventListener(
        'change',
        updateSpecialDiscountState
    );

    updateSpecialDiscountState();
});
</script>