@extends('portal.layouts.app')

@section('content')
@include('portal.spin-campaigns._style')

<div class="spin-campaign-page">
    <div class="campaign-header">
        <div>
            <h1>Edit Spin Campaign</h1>
            <p>Update campaign settings and monthly case-based spin rules.</p>
        </div>

        <a href="{{ route('portal.spin-campaigns.show', $campaign) }}" class="btn btn-light">← Back</a>
    </div>

    <div class="campaign-stats-grid">
        <div class="stat-card">
            <span>Total Cases</span>
            <strong>{{ number_format($campaign->total_cases) }}</strong>
        </div>

        <div class="stat-card">
            <span>Spins / Case</span>
            <strong>{{ $campaign->spins_per_case }}</strong>
        </div>

        <div class="stat-card">
            <span>Normal Total</span>
            <strong>{{ $campaign->normal_discount_total }}%</strong>
        </div>

        <div class="stat-card">
            <span>Progress</span>
            <strong>{{ number_format($campaign->total_spins_used) }}</strong>
        </div>

        <div class="stat-card">
            <span>Status</span>
            <strong>
                @if((string)$campaign->status === '1' || $campaign->status === 'active')
                    Active
                @else
                    Inactive
                @endif
            </strong>
        </div>
    </div>

    <div class="campaign-card">
        <form method="POST" action="{{ route('portal.spin-campaigns.update', $campaign) }}">
            @method('PUT')
            @include('portal.spin-campaigns._form', [
                'buttonText' => 'Update Campaign'
            ])
        </form>
    </div>
</div>
@endsection