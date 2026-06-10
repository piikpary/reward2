@extends('portal.layouts.app')

@section('content')
    <h1 class="page-title">Edit Spin Reward</h1>
    <p class="page-subtitle">Update discount percentage and winning chance.</p>

    <div class="card profile-card">
        <form method="POST" action="{{ route('portal.spin-rewards.update', $spinReward) }}">
            @csrf
            @method('PUT')

            <div class="card-body">
                <div class="form-row">
                    <label class="form-label">
                        <span class="icon">%</span>
                        <span>Discount Percentage</span>
                    </label>

                    <div class="field-wrap">
                        <input type="number" name="discount_percentage" class="form-control"
                               value="{{ old('discount_percentage', $spinReward->discount_percentage) }}" min="1" max="100" required>
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
                               value="{{ old('chance_weight', $spinReward->chance_weight) }}" min="0" required>
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
                               value="{{ old('sort_order', $spinReward->sort_order) }}" min="0">
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
                            <option value="1" {{ old('status', $spinReward->status) == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status', $spinReward->status) == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="save-btn">Update Reward</button>
                <a href="{{ route('portal.spin-rewards.index') }}" class="quick-btn secondary" style="margin-left: 8px;">Cancel</a>
            </div>
        </form>
    </div>
@endsection