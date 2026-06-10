@extends('portal.layouts.app')

@section('content')
    <div class="dashboard-header">
        <div>
            <h1 class="page-title">Customer Wallet</h1>
            <p class="page-subtitle">{{ $user->phone_number }} wallet balance, spin and discount management.</p>
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

    <div class="wallet-form-grid">
        <div class="card profile-card">
            <form method="POST" action="{{ route('portal.customers.add-spin', $user) }}">
                @csrf

                <div class="card-body">
                    <h3 class="form-title">Add Spin Quantity</h3>
                    <p class="form-desc">
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

        <div class="card profile-card">
            <form method="POST" action="{{ route('portal.customers.add-discount', $user) }}">
                @csrf

                <div class="card-body">
                    <h3 class="form-title">Add Discount Percentage</h3>
                    <p class="form-desc">
                        Add discount percentage to this customer. It will show in the mobile discount list.
                    </p>

                    <div class="form-row">
                        <label class="form-label">
                            <span class="icon">%</span>
                            <span>Discount %</span>
                        </label>

                        <div class="field-wrap">
                            <input
                                type="number"
                                name="discount_percentage"
                                class="form-control"
                                value="{{ old('discount_percentage', 5) }}"
                                min="1"
                                max="100"
                                step="0.01"
                                required
                            >
                            @error('discount_percentage')
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
                                placeholder="Example: Manual reward, campaign bonus"
                            >
                            @error('description')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="save-btn">Add Discount</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .wallet-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            align-items: start;
        }

        .form-title {
            margin: 0;
            color: #000;
            font-size: 18px;
            font-weight: 700;
        }

        .form-desc {
            color: #6b7280;
            margin: 8px 0 20px;
            font-size: 14px;
            line-height: 1.5;
        }

        @media (max-width: 900px) {
            .wallet-form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection