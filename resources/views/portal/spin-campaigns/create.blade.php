@extends('portal.layouts.app')

@section('content')
@include('portal.spin-campaigns._style')

<div class="spin-campaign-page">
    <div class="campaign-header">
        <div>
            <h1>Add Main Campaign</h1>

            <p>
                Create the main campaign period first.
                You can add multiple subcampaign rules after saving.
            </p>
        </div>

        <a
            href="{{ route('portal.spin-campaigns.index') }}"
            class="btn btn-light"
        >
            ← Back
        </a>
    </div>

    <div class="campaign-guide-grid">
        <div class="guide-card">
            <div class="guide-icon purple">📅</div>

            <div>
                <strong>Shared campaign period</strong>

                <p>
                    All subcampaigns will operate inside this start and end date.
                </p>
            </div>
        </div>

        <div class="guide-card">
            <div class="guide-icon green">📂</div>

            <div>
                <strong>Multiple subcampaigns</strong>

                <p>
                    Each subcampaign can have different cases, spins, and discount totals.
                </p>
            </div>
        </div>

        <div class="guide-card">
            <div class="guide-icon blue">🧾</div>

            <div>
                <strong>Reward source</strong>

                <p>
                    Spin discounts are selected from the active Discount List.
                </p>
            </div>
        </div>
    </div>

    <div class="campaign-card">
        <form
            method="POST"
            action="{{ route('portal.spin-campaigns.store') }}"
        >
            @include('portal.spin-campaigns._form', [
                'buttonText' => 'Save Main Campaign',
            ])
        </form>
    </div>
</div>
@endsection