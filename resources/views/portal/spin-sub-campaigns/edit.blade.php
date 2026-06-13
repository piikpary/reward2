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
        .form-grid {
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

<div class="sub-page">
    <div class="sub-header">
        <div>
            <h1 class="sub-title">Edit Subcampaign</h1>
            <p class="sub-description">
                Update the case and spin rule inside this main campaign period.
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

    @if ($errors->any())
        <div class="validation-box">
            <strong>Please correct the following information:</strong>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        method="POST"
        action="{{ route(
            'portal.spin-campaigns.sub-campaigns.update',
            [$campaign, $subCampaign]
        ) }}"
        class="form-card"
    >
        @csrf
        @method('PUT')

        <div class="form-card-header">
            <h2>Subcampaign Rule</h2>
            <p>
                Update total cases, spins per case, discount target, priority,
                and status for this subcampaign.
            </p>
        </div>

        <div class="form-body">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label class="form-label" for="name">
                        Subcampaign Name <span class="required">*</span>
                    </label>

                    <input
                        id="name"
                        type="text"
                        name="name"
                        class="form-control"
                        value="{{ old('name', $subCampaign->name) }}"
                        required
                    >

                    @error('name')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="total_cases">
                        Total Cases <span class="required">*</span>
                    </label>

                    <input
                        id="total_cases"
                        type="number"
                        name="total_cases"
                        class="form-control"
                        value="{{ old('total_cases', $subCampaign->total_cases) }}"
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
                        Spins Per Case <span class="required">*</span>
                    </label>

                    <input
                        id="spins_per_case"
                        type="number"
                        name="spins_per_case"
                        class="form-control"
                        value="{{ old('spins_per_case', $subCampaign->spins_per_case) }}"
                        min="1"
                        required
                    >

                    <p class="helper-text">
                        Number of spins assigned to each case.
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
                            value="{{ old(
                                'normal_discount_total',
                                $subCampaign->normal_discount_total
                            ) }}"
                            min="1"
                            required
                        >

                        <span class="input-suffix">%</span>
                    </div>

                    <p class="helper-text">
                        All spins in a completed normal case must total this value.
                    </p>

                    @error('normal_discount_total')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="priority">
                        Priority <span class="required">*</span>
                    </label>

                    <input
                        id="priority"
                        type="number"
                        name="priority"
                        class="form-control"
                        value="{{ old('priority', $subCampaign->priority) }}"
                        min="1"
                        required
                    >

                    <p class="helper-text">
                        Higher-priority active subcampaigns are selected first.
                    </p>

                    @error('priority')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="status">
                        Status <span class="required">*</span>
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
                                old('status', $subCampaign->status) === 'active'
                            )
                        >
                            Active
                        </option>

                        <option
                            value="inactive"
                            @selected(
                                old('status', $subCampaign->status) === 'inactive'
                            )
                        >
                            Inactive
                        </option>
                    </select>

                    @error('status')
                        <span class="error-text">{{ $message }}</span>
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
                    >{{ old('description', $subCampaign->description) }}</textarea>

                    @error('description')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-footer">
            <a
                href="{{ route('portal.spin-campaigns.sub-campaigns.index', $campaign) }}"
                class="cancel-btn"
            >
                Cancel
            </a>

            <button type="submit" class="save-btn">
                Update Subcampaign
            </button>
        </div>
    </form>
</div>
@endsection