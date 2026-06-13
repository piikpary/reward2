@extends('portal.layouts.app')

@section('content')
@include('portal.spin-campaigns._style')

<div class="spin-campaign-page">
    <div class="campaign-header">
        <div>
            <h1>Edit Main Campaign</h1>

            <p>
                Update the shared campaign information and active period.
                Subcampaign spin rules are managed separately.
            </p>
        </div>

        <a
            href="{{ route('portal.spin-campaigns.show', $campaign) }}"
            class="btn btn-light"
        >
            ← Back
        </a>
    </div>

    @php
        $subCampaigns = $campaign->subCampaigns ?? collect();

        $combinedCases = $subCampaigns->sum(function ($subCampaign) {
            return (int) $subCampaign->total_cases;
        });

        $totalSpinQuota = $subCampaigns->sum(function ($subCampaign) {
            return
                (int) $subCampaign->total_cases
                * (int) $subCampaign->spins_per_case;
        });

        $totalSpinsUsed = $subCampaigns->sum(function ($subCampaign) {
            return (int) $subCampaign->total_spins_used;
        });

        $isActive =
            (string) $campaign->status === '1'
            || $campaign->status === 'active';
    @endphp

    <div class="campaign-stats-grid">
        <div class="stat-card">
            <span>Subcampaigns</span>

            <strong>
                {{ number_format($subCampaigns->count()) }}
            </strong>
        </div>

        <div class="stat-card">
            <span>Combined Cases</span>

            <strong>
                {{ number_format($combinedCases) }}
            </strong>
        </div>

        <div class="stat-card">
            <span>Total Spin Quota</span>

            <strong>
                {{ number_format($totalSpinQuota) }}
            </strong>
        </div>

        <div class="stat-card">
            <span>Total Spins Used</span>

            <strong>
                {{ number_format($totalSpinsUsed) }}
            </strong>
        </div>

        <div class="stat-card">
            <span>Status</span>

            <strong>
                {{ $isActive ? 'Active' : 'Inactive' }}
            </strong>
        </div>
    </div>

    <div class="campaign-card">
        <form
            method="POST"
            action="{{ route('portal.spin-campaigns.update', $campaign) }}"
        >
            @method('PUT')

            @include('portal.spin-campaigns._form', [
                'buttonText' => 'Update Main Campaign',
            ])
        </form>
    </div>
</div>
@endsection