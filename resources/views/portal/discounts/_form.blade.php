<div class="card profile-card">
    <div class="card-body">
        <div class="form-row">
            <label class="form-label">
                <span class="icon">%</span>
                <span>Discount Percentage</span>
            </label>

            <div class="field-wrap">
                <input
                    type="number"
                    name="discount_percentage"
                    class="form-control"
                    value="{{ old('discount_percentage', $discount->discount_percentage ?? '') }}"
                    min="1"
                    max="100"
                    required
                >

                @error('discount_percentage')
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
                    <option value="active" {{ old('status', $discount->status ?? 'active') === 'active' ? 'selected' : '' }}>
                        Active
                    </option>
                    <option value="inactive" {{ old('status', $discount->status ?? '') === 'inactive' ? 'selected' : '' }}>
                        Inactive
                    </option>
                </select>

                @error('status')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="card-footer">
        <button type="submit" class="save-btn">Save Discount</button>
    </div>
</div>