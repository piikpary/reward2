@php
    $startValue = old('start_date', $campaign->start_date ? \Carbon\Carbon::parse($campaign->start_date)->format('Y-m-d\TH:i') : '');
    $endValue = old('end_date', $campaign->end_date ? \Carbon\Carbon::parse($campaign->end_date)->format('Y-m-d\TH:i') : '');
    $buttonText = $buttonText ?? 'Save Campaign';
@endphp

@csrf

<div class="campaign-form-grid">
    <div class="form-group">
        <label>Campaign Name <span class="required">*</span></label>
        <input type="text" name="name" value="{{ old('name', $campaign->name) }}" placeholder="Enter campaign name" required>
        @error('name') <small class="error">{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label>Status <span class="required">*</span></label>
        <select name="status" required>
            <option value="1" @selected((string) old('status', $campaign->status ?? 1) === '1' || old('status', $campaign->status ?? 1) === 'active')>Active</option>
            <option value="0" @selected((string) old('status', $campaign->status ?? 1) === '0' || old('status', $campaign->status ?? 1) === 'inactive')>Inactive</option>
        </select>
        <small class="help-text">Inactive campaigns will not run.</small>
        @error('status') <small class="error">{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label>Start Date <span class="required">*</span></label>
        <input type="datetime-local" name="start_date" value="{{ $startValue }}" required>
        <small class="help-text">Campaign start date and time.</small>
        @error('start_date') <small class="error">{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label>End Date <span class="required">*</span></label>
        <input type="datetime-local" name="end_date" value="{{ $endValue }}" required>
        <small class="help-text">Campaign end date and time.</small>
        @error('end_date') <small class="error">{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label>Total Cases <span class="required">*</span></label>
        <input type="number" name="total_cases" value="{{ old('total_cases', $campaign->total_cases ?? 1000) }}" min="1" required>
        <small class="help-text">Total number of cases in this campaign.</small>
        @error('total_cases') <small class="error">{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label>Spins Per Case <span class="required">*</span></label>
        <input type="number" name="spins_per_case" value="{{ old('spins_per_case', $campaign->spins_per_case ?? 4) }}" min="1" required>
        <small class="help-text">Number of spins awarded per case.</small>
        @error('spins_per_case') <small class="error">{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label>Normal Discount Total Per Case (%) <span class="required">*</span></label>
        <div class="input-suffix">
            <input type="number" name="normal_discount_total" value="{{ old('normal_discount_total', $campaign->normal_discount_total ?? 30) }}" min="1" required>
            <span>%</span>
        </div>
        <small class="help-text">Sum of normal discount percentages across all spins.</small>
        @error('normal_discount_total') <small class="error">{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label>Priority <span class="required">*</span></label>
        <input type="number" name="priority" value="{{ old('priority', $campaign->priority ?? 1) }}" min="0" required>
        <small class="help-text">Higher priority campaign will be selected first.</small>
        @error('priority') <small class="error">{{ $message }}</small> @enderror
    </div>

    <div class="form-group full">
        <label>Description</label>
        <textarea name="description" rows="4" placeholder="Enter campaign description (optional)">{{ old('description', $campaign->description) }}</textarea>
        <small class="help-text">Optional notes about this campaign.</small>
        @error('description') <small class="error">{{ $message }}</small> @enderror
    </div>
</div>

<div class="form-divider"></div>

<div class="form-actions">
    <button type="submit" class="btn btn-purple">💾 {{ $buttonText }}</button>
    <a href="{{ route('portal.spin-campaigns.index') }}" class="btn btn-light">✕ Cancel</a>
</div>