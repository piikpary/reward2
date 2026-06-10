@extends('portal.layouts.app')

@section('content')
    <h1 class="page-title">Add Spin Reward</h1>
    <p class="page-subtitle">Create a discount reward for the spin wheel.</p>

    <div class="card profile-card">
        <form method="POST" action="{{ route('portal.spin-rewards.store') }}">
            @csrf

            <div class="card-body">
                <div class="form-row">
                    <label class="form-label">
                        <span class="icon">%</span>
                        <span>Discount Percentage</span>
                    </label>

                    <div class="field-wrap">
                        <input type="number" name="discount_percentage" class="form-control"
                               value="{{ old('discount_percentage') }}" min="1" max="100" placeholder="Example: 20" required>
                        @error('discount_percentage') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label">
                        <span class="icon">◉</span>
                        <span>Chance Weight</span>
                    </label>

                    <div class="field-wrap">
                        <input type="number" name="chance_weight" class="form-control"
                               value="{{ old('chance_weight', 1) }}" min="0" placeholder="Example: 10" required>
                        @error('chance_weight') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label">
                        <span class="icon">#</span>
                        <span>Sort Order</span>
                    </label>

                    <div class="field-wrap">
                        <input type="number" name="sort_order" class="form-control"
                               value="{{ old('sort_order', 0) }}" min="0">
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
                            <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="save-btn">Save Reward</button>
                <a href="{{ route('portal.spin-rewards.index') }}" class="quick-btn secondary" style="margin-left: 8px;">Cancel</a>
            </div>
        </form>
    </div>
@endsection