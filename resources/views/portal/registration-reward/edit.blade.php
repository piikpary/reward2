@extends('portal.layouts.app')

@section('title', 'Registration Reward')

@section('content')
    <div class="campaign-card">
        <div
            style="
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 20px;
                margin-bottom: 24px;
            "
        >
            <div>
                <h1 style="margin: 0 0 8px;">
                    Registration Reward
                </h1>

                <p class="muted" style="margin: 0;">
                    Automatically give a Spin or Discount
                    welcome gift only to newly registered
                    mobile application users.
                </p>
            </div>
        </div>

        @if (session('success'))
            <div class="success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>
                            {{ $error }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            method="POST"
            action="{{ route(
                'portal.registration-reward.update'
            ) }}"
        >
            @csrf
            @method('PUT')

            <div
                style="
                    margin-bottom: 24px;
                    padding: 18px;
                    border: 1px solid #e5e7eb;
                    border-radius: 14px;
                "
            >
                <label
                    style="
                        display: flex;
                        align-items: center;
                        gap: 10px;
                        font-weight: 700;
                    "
                >
                    <input
                        type="checkbox"
                        name="is_enabled"
                        value="1"
                        @checked(
                            old(
                                'is_enabled',
                                $setting->is_enabled
                            )
                        )
                    >

                    Enable registration welcome reward
                </label>
            </div>

            <div
                style="
                    display: grid;
                    grid-template-columns:
                        repeat(2, minmax(0, 1fr));
                    gap: 20px;
                "
            >
                <div>
                    <label
                        for="wallet_type"
                        style="
                            display: block;
                            margin-bottom: 8px;
                            font-weight: 700;
                        "
                    >
                        Reward Type
                    </label>

                    <select
                        id="wallet_type"
                        name="wallet_type"
                        class="form-control"
                        required
                    >
                        <option
                            value="spin"
                            @selected(
                                old(
                                    'wallet_type',
                                    $setting->wallet_type
                                ) === 'spin'
                            )
                        >
                            Spin
                        </option>

                        <option
                            value="discount"
                            @selected(
                                old(
                                    'wallet_type',
                                    $setting->wallet_type
                                ) === 'discount'
                            )
                        >
                            Discount
                        </option>
                    </select>
                </div>

                <div>
                    <label
                        for="amount"
                        style="
                            display: block;
                            margin-bottom: 8px;
                            font-weight: 700;
                        "
                    >
                        Reward Amount
                    </label>

                    <input
                        id="amount"
                        name="amount"
                        type="number"
                        min="1"
                        step="0.01"
                        class="form-control"
                        value="{{ old(
                            'amount',
                            $setting->amount
                        ) }}"
                        required
                    >
                </div>
            </div>

            <div style="margin-top: 24px;">
                <button
                    type="submit"
                    class="button button-primary"
                >
                    Save Registration Reward
                </button>
            </div>
        </form>
    </div>
@endsection