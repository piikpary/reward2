@extends('portal.layouts.app')

@section('content')
@include('portal.spin-campaigns._style')

<div class="spin-campaign-page">
    <div class="campaign-header">
        <div>
            <h1>Add Spin Campaign</h1>
            <p>Create a monthly case-based spin rule and define how discounts are distributed.</p>
        </div>

        <a href="{{ route('portal.spin-campaigns.index') }}" class="btn btn-light">← Back</a>
    </div>

    <div class="campaign-guide-grid">
        <div class="guide-card">
            <div class="guide-icon purple">🎡</div>
            <div>
                <strong>4 spins / case</strong>
                <p>Each case gets 4 spins.</p>
            </div>
        </div>

        <div class="guide-card">
            <div class="guide-icon green">%</div>
            <div>
                <strong>Normal total 30%</strong>
                <p>Example: 10% + 5% + 5% + 10% = 30%</p>
            </div>
        </div>

        <div class="guide-card">
            <div class="guide-icon blue">🧾</div>
            <div>
                <strong>Reward source</strong>
                <p>Discount values come from active Discount List.</p>
            </div>
        </div>
    </div>

    <div class="campaign-card">
        <form method="POST" action="{{ route('portal.spin-campaigns.store') }}">
            @include('portal.spin-campaigns._form', [
                'buttonText' => 'Save Campaign'
            ])
        </form>
    </div>
</div>
@endsection