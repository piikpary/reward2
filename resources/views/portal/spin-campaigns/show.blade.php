@extends('portal.layouts.app')

@section('content')
@include('portal.spin-campaigns._style')

@php
    // Controller sends this page a variable named $campaign.
    // Keep the existing Blade code working with $spinCampaign.
    $spinCampaign = $campaign;

    $totalAllowed =
        ($spinCampaign->total_cases ?? 0)
        * ($spinCampaign->spins_per_case ?? 0);

    $used = $spinCampaign->total_spins_used ?? 0;

    $progress = $totalAllowed > 0
        ? round(($used / $totalAllowed) * 100, 2)
        : 0;
@endphp

<div class="spin-campaign-page">
    <div class="campaign-header">
        <div>
            <h1>{{ $spinCampaign->name }}</h1>
            <p>Review campaign detail, progress, and special case configuration.</p>
        </div>

        <div class="form-actions">
            <a href="{{ route('portal.spin-campaigns.index') }}" class="btn btn-light">← Back</a>
            <a href="{{ route('portal.spin-campaigns.edit', $spinCampaign) }}" class="btn btn-dark">Edit Campaign</a>
        </div>
    </div>

    @if(session('success'))
        <div class="success">{{ session('success') }}</div>
    @endif

    <div class="campaign-stats-grid">
        <div class="stat-card">
            <span>Total Cases</span>
            <strong>{{ number_format($spinCampaign->total_cases) }}</strong>
        </div>

        <div class="stat-card">
            <span>Spins / Case</span>
            <strong>{{ $spinCampaign->spins_per_case }}</strong>
        </div>

        <div class="stat-card">
            <span>Normal Total</span>
            <strong>{{ $spinCampaign->normal_discount_total }}%</strong>
        </div>

        <div class="stat-card">
            <span>Progress</span>
            <strong>{{ number_format($used) }} / {{ number_format($totalAllowed) }}</strong>
        </div>

        <div class="stat-card">
            <span>Special Cases</span>
            <strong>{{ $spinCampaign->specialCases->count() }}</strong>
        </div>
    </div>

    <div class="rule-box">
        <h3>Rule Explanation</h3>
        <p>
            Normal case: every <strong>{{ $spinCampaign->spins_per_case }}</strong> spins must equal
            <strong>{{ $spinCampaign->normal_discount_total }}%</strong>.
            Example: 10% + 5% + 5% + 10% = {{ $spinCampaign->normal_discount_total }}%.
        </p>
        <p>
            Discount values are selected from the active <strong>Discount List</strong>.
            Special case can override normal total. Example: case 595 = 100%.
        </p>

        <form action="{{ route('portal.spin-campaigns.reset-progress', $spinCampaign) }}" method="POST" onsubmit="return confirm('Reset all spin progress for this campaign?')">
            @csrf
            <button class="btn btn-danger" style="margin-top: 14px;">Reset Campaign Progress</button>
        </form>
    </div>

    <div class="campaign-card">
        <h3>Add / Update Special Case</h3>

        <form method="POST" action="{{ route('portal.spin-campaigns.special-cases.store', $spinCampaign) }}" class="special-form">
            @csrf

            <div class="form-group">
                <label>Case Number <span class="required">*</span></label>
                <input type="number" name="case_number" min="1" max="{{ $spinCampaign->total_cases }}" placeholder="Example: 595" required>
                @error('case_number') <small class="error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label>Total Discount (%) <span class="required">*</span></label>
                <input type="number" name="total_discount" min="1" placeholder="Example: 100" required>
                @error('total_discount') <small class="error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label>Status <span class="required">*</span></label>
                <select name="status" required>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
                @error('status') <small class="error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <button class="btn btn-purple" type="submit">Save Special Case</button>
            </div>
        </form>
    </div>

    <div class="campaign-card">
        <h3>Special Cases</h3>

        <table class="campaign-table">
            <thead>
                <tr>
                    <th>Case Number</th>
                    <th>Total Discount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($spinCampaign->specialCases as $specialCase)
                    <tr>
                        <td><strong>Case {{ $specialCase->case_number }}</strong></td>
                        <td>{{ $specialCase->total_discount }}%</td>
                        <td>
                            @if((string)$specialCase->status === '1' || $specialCase->status === 'active')
                                <span class="badge active">Active</span>
                            @else
                                <span class="badge inactive">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <form method="POST" action="{{ route('portal.spin-campaigns.special-cases.delete', [$spinCampaign, $specialCase]) }}" class="inline-form" onsubmit="return confirm('Delete this special case?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="empty-state">No special cases yet.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection