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

    <div class="form-group full">
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
            If main campaign periods overlap, the campaign with higher priority
            will be selected first.
        </small>

        @error('priority')
            <small class="error">{{ $message }}</small>
        @enderror
    </div>

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