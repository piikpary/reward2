@php
    $startValue = old(
        'start_date',
        $campaign->start_date
            ? \Carbon\Carbon::parse($campaign->start_date)->format('Y-m-d\TH:i')
            : ''
    );

    $endValue = old(
        'end_date',
        $campaign->end_date
            ? \Carbon\Carbon::parse($campaign->end_date)->format('Y-m-d\TH:i')
            : ''
    );

    $currentStatus = old(
        'status',
        $campaign->status ?? 1
    );

    /*
     * Main campaign hidden special-spin reward.
     * This is available only when editing an existing campaign.
     */
    $unusedMainSpecialRewards = $campaign->exists
    ? $campaign->mainSpecialRewards
        ->filter(function ($reward) {
            return !(bool) $reward->is_used;
        })
        ->values()
    : collect();

$usedMainSpecialRewards = $campaign->exists
    ? $campaign->mainSpecialRewards
        ->filter(function ($reward) {
            return (bool) $reward->is_used;
        })
        ->values()
        : collect();

    $specialDiscountValues = old(
        'special_discounts',
        $unusedMainSpecialRewards
            ->pluck('special_discount')
            ->toArray()
    );

    if (
        !is_array($specialDiscountValues)
        || empty($specialDiscountValues)
    ) {
        $specialDiscountValues = [''];
    }

    $specialDiscountEnabled = (bool) old(
        'special_discount_enabled',
        $unusedMainSpecialRewards->isNotEmpty()
    );

    /*
     * Main campaign special spin can only be enabled
     * after at least one subcampaign exists.
     */
    $hasSubCampaigns = $campaign->exists
        && $campaign->subCampaigns->isNotEmpty();

    $buttonText = $buttonText ?? 'Save Main Campaign';
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

<div class="campaign-form-grid">
    <div class="form-group">
        <label for="name">
            Campaign Name <span class="required">*</span>
        </label>

        <input
            id="name"
            type="text"
            name="name"
            value="{{ old('name', $campaign->name) }}"
            placeholder="Example: Ganzberg June Campaign"
            required
        >

        <small class="help-text">
            This is the main campaign name shared by all subcampaigns.
        </small>

        @error('name')
            <small class="error">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group">
        <label for="status">
            Status <span class="required">*</span>
        </label>

        <select
            id="status"
            name="status"
            required
        >
            <option
                value="1"
                @selected(
                    (string) $currentStatus === '1'
                    || $currentStatus === 'active'
                )
            >
                Active
            </option>

            <option
                value="0"
                @selected(
                    (string) $currentStatus === '0'
                    || $currentStatus === 'inactive'
                )
            >
                Inactive
            </option>
        </select>

        <small class="help-text">
            Inactive campaigns and their subcampaigns will not run.
        </small>

        @error('status')
            <small class="error">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group">
        <label for="start_date">
            Start Date <span class="required">*</span>
        </label>

        <input
            id="start_date"
            type="datetime-local"
            name="start_date"
            value="{{ $startValue }}"
            required
        >

        <small class="help-text">
            All subcampaigns will use this main campaign start date.
        </small>

        @error('start_date')
            <small class="error">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group">
        <label for="end_date">
            End Date <span class="required">*</span>
        </label>

        <input
            id="end_date"
            type="datetime-local"
            name="end_date"
            value="{{ $endValue }}"
            required
        >

        <small class="help-text">
            All subcampaigns must operate within this period.
        </small>

        @error('end_date')
            <small class="error">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group">
        <label for="priority">
            Priority <span class="required">*</span>
        </label>

        <input
            id="priority"
            type="number"
            name="priority"
            value="{{ old('priority', $campaign->priority ?? 1) }}"
            min="0"
            required
        >

        <small class="help-text">
            If campaign periods overlap, the campaign with higher priority
            will be selected first.
        </small>

        @error('priority')
            <small class="error">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group">
        <label for="max_spin_qty">
            Maximum Spin Quantity Per Request
            <span class="required">*</span>
        </label>

        <input
            id="max_spin_qty"
            type="number"
            name="max_spin_qty"
            value="{{ old('max_spin_qty', $campaign->max_spin_qty ?? 4) }}"
            min="1"
            required
        >

        <small class="help-text">
            Maximum quantity a mobile user can request in one API call.
            Example: set 4 to allow quantities 1, 2, 3, or 4.
        </small>

        @error('max_spin_qty')
            <small class="error">{{ $message }}</small>
        @enderror
    </div>

    @if($campaign->exists)
    <div
        id="main-special-spin-box"
        class="form-group full main-special-spin-box"
    >
        <h3 class="main-special-spin-heading">
            Main Campaign Special Spin Discounts
        </h3>

        <input
            type="hidden"
            name="special_discount_enabled"
            value="0"
        >

        <label
            for="special_discount_enabled"
            class="main-special-checkbox"
        >
            <input
                id="special_discount_enabled"
                type="checkbox"
                name="special_discount_enabled"
                value="1"
                @checked($specialDiscountEnabled)
                @disabled(!$hasSubCampaigns)
            >

            Enable Main Campaign Special Spin Discounts
        </label>

        <small class="help-text">
            Each special reward receives its own hidden random
            position inside one of this campaign's active
            subcampaigns. These rewards do not increase the total
            spin quota.
        </small>

        @if(!$hasSubCampaigns)
            <small class="error">
                Create at least one subcampaign before enabling
                main campaign special spin discounts.
            </small>
        @endif

        @error('special_discount_enabled')
            <small class="error">
                {{ $message }}
            </small>
        @enderror

        <div
            id="main-special-discount-list"
            class="main-special-discount-list"
        >
            @foreach($specialDiscountValues as $discountValue)
                <div class="main-special-discount-row">
                    <div class="main-special-discount-field">
                        <label>
                            Special Spin Discount (%)
                            <span class="required">*</span>
                        </label>

                        <div class="main-special-input-wrap">
                            <input
                                type="number"
                                name="special_discounts[]"
                                class="main-special-discount-input"
                                value="{{ $discountValue }}"
                                min="0.01"
                                step="0.01"
                                placeholder="Example: 1000"
                                @disabled(!$hasSubCampaigns)
                            >

                            <span class="main-special-input-suffix">
                                %
                            </span>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="remove-main-special-btn"
                        @disabled(!$hasSubCampaigns)
                    >
                        Remove
                    </button>
                </div>
            @endforeach
        </div>

        <button
            type="button"
            id="add-main-special-discount"
            class="add-main-special-btn"
            @disabled(!$hasSubCampaigns)
        >
            + Add Special Discount
        </button>

        <small class="help-text">
            Each row creates one separate reward, hidden spin
            position, and unique verification code.
        </small>

        @error('special_discounts')
            <small class="error">
                {{ $message }}
            </small>
        @enderror

        @error('special_discounts.*')
            <small class="error">
                {{ $message }}
            </small>
        @enderror

        @if($usedMainSpecialRewards->isNotEmpty())
            <div class="main-awarded-rewards">
                <h4>
                    Already Awarded Special Rewards
                </h4>

                @foreach($usedMainSpecialRewards as $usedReward)
                    <div class="main-awarded-item">
                        <strong>
                            {{
                                number_format(
                                    (float) $usedReward
                                        ->special_discount,
                                    2
                                )
                            }}%
                        </strong>

                        <span>
                            Code:
                            {{ $usedReward->reward_code ?? '-' }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endif

    <div class="form-group full">
        <label for="description">
            Description
        </label>

        <textarea
            id="description"
            name="description"
            rows="4"
            placeholder="Enter optional information about this main campaign"
        >{{ old('description', $campaign->description) }}</textarea>

        <small class="help-text">
            Case quantity, spins per case, and discount totals are configured
            inside the subcampaigns.
        </small>

        @error('description')
            <small class="error">{{ $message }}</small>
        @enderror
    </div>
</div>

<div class="form-divider"></div>

<div class="form-actions">
    <button
        type="submit"
        class="btn btn-purple"
    >
        💾 {{ $buttonText }}
    </button>

    <a
        href="{{ route('portal.spin-campaigns.index') }}"
        class="btn btn-light"
    >
        ✕ Cancel
    </a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const enabledCheckbox = document.getElementById(
        'special_discount_enabled'
    );

    const box = document.getElementById(
        'main-special-spin-box'
    );

    const list = document.getElementById(
        'main-special-discount-list'
    );

    const addButton = document.getElementById(
        'add-main-special-discount'
    );

    if (
        !enabledCheckbox
        || !box
        || !list
        || !addButton
    ) {
        return;
    }

    const hasSubCampaigns = @json($hasSubCampaigns);

    function createRow() {
        const row = document.createElement('div');

        row.className = 'main-special-discount-row';

        row.innerHTML = `
            <div class="main-special-discount-field">
                <label>
                    Special Spin Discount (%)
                    <span class="required">*</span>
                </label>

                <div class="main-special-input-wrap">
                    <input
                        type="number"
                        name="special_discounts[]"
                        class="main-special-discount-input"
                        min="0.01"
                        step="0.01"
                        placeholder="Example: 1000"
                    >

                    <span class="main-special-input-suffix">
                        %
                    </span>
                </div>
            </div>

            <button
                type="button"
                class="remove-main-special-btn"
            >
                Remove
            </button>
        `;

        return row;
    }

    function updateState() {
        const enabled =
            hasSubCampaigns
            && enabledCheckbox.checked;

        box.classList.toggle(
            'main-special-disabled',
            !enabled
        );

        list.querySelectorAll(
            '.main-special-discount-input'
        ).forEach(function (input) {
            input.disabled = !enabled;
            input.required = enabled;
        });

        list.querySelectorAll(
            '.remove-main-special-btn'
        ).forEach(function (button) {
            button.disabled = !enabled;
        });

        addButton.disabled = !enabled;
    }

    addButton.addEventListener('click', function () {
        list.appendChild(createRow());

        updateState();
    });

    list.addEventListener('click', function (event) {
        if (
            !event.target.classList.contains(
                'remove-main-special-btn'
            )
        ) {
            return;
        }

        const rows = list.querySelectorAll(
            '.main-special-discount-row'
        );

        const currentRow = event.target.closest(
            '.main-special-discount-row'
        );

        if (rows.length > 1) {
            currentRow.remove();
        } else {
            currentRow.querySelector(
                '.main-special-discount-input'
            ).value = '';
        }
    });

    enabledCheckbox.addEventListener(
        'change',
        updateState
    );

    updateState();
});
</script>