@extends('portal.layouts.app')

@section('content')
    <div class="dashboard-header">
        <div>
            <h1 class="page-title">Customer Wallet</h1>
            <p class="page-subtitle">{{ $user->phone_number }} wallet balance and spin management.</p>
        </div>

        <a href="{{ route('portal.customers.index') }}" class="quick-btn secondary">
            Back
        </a>
    </div>

    @if (session('success'))
        <div class="success">{{ session('success') }}</div>
    @endif

    <div class="dashboard-grid" style="margin-bottom: 24px;">
        @foreach ($user->userWallets as $userWallet)
            <div class="dashboard-card">
                <div class="dashboard-icon">
                    {{ $userWallet->wallet->type === 'spin' ? '🎡' : '%' }}
                </div>
                <h3>{{ $userWallet->wallet->name }}</h3>
                <h2>{{ number_format($userWallet->balance, 2) }}</h2>
                <p>{{ ucfirst($userWallet->wallet->type) }} balance</p>
            </div>
        @endforeach
    </div>

    <div class="card profile-card">
        <form method="POST" action="{{ route('portal.customers.add-spin', $user) }}">
            @csrf

            <div class="card-body">
                <h3 style="margin-top: 0;">Add Spin Quantity</h3>
                <p style="color: #6b7280; margin-top: 6px;">
                    Add spin quantity to this customer. Customer can use this spin in the mobile app.
                </p>

                <div class="form-row">
                    <label class="form-label">
                        <span class="icon">🎡</span>
                        <span>Spin Qty</span>
                    </label>

                    <div class="field-wrap">
                        <input
                            type="number"
                            name="qty"
                            class="form-control"
                            value="{{ old('qty', 1) }}"
                            min="1"
                            required
                        >
                        @error('qty')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label">
                        <span class="icon">✎</span>
                        <span>Description</span>
                    </label>

                    <div class="field-wrap">
                        <input
                            type="text"
                            name="description"
                            class="form-control"
                            value="{{ old('description') }}"
                            placeholder="Example: Campaign reward, manual top up"
                        >
                        @error('description')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="save-btn">Add Spin</button>
            </div>
        </form>
    </div>
@endsection