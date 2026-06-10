@extends('portal.layouts.app')

@section('content')
    <h1 class="page-title">User Profile</h1>
    <p class="page-subtitle">Manage your personal information and account settings.</p>

    <div class="card profile-card">
        <form method="POST" action="{{ route('portal.profile.update') }}">
            @csrf
            @method('PUT')

            <div class="card-body">
                @if (session('success'))
                    <div class="success">{{ session('success') }}</div>
                @endif

                <div class="form-row">
                    <label class="form-label">
                        <span class="icon">♙</span>
                        <span>Name</span>
                    </label>

                    <div class="field-wrap">
                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            value="{{ old('name', $user->name) }}"
                            required
                        >
                        @error('name')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label">
                        <span class="icon">✉</span>
                        <span>Email</span>
                    </label>

                    <div class="field-wrap">
                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            value="{{ old('email', $user->email) }}"
                            required
                        >
                        @error('email')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label">
                        <span class="icon">☎</span>
                        <span>Phone Number</span>
                    </label>

                    <div class="field-wrap">
                        <input
                            type="text"
                            name="phone_number"
                            class="form-control"
                            placeholder="Enter phone number"
                            value="{{ old('phone_number', $user->phone_number) }}"
                        >
                        @error('phone_number')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="divider"></div>

                <div class="form-row">
                    <label class="form-label">
                        <span class="icon">▣</span>
                        <span>New Password</span>
                    </label>

                    <div class="field-wrap">
                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            placeholder="Leave blank if no change"
                        >
                        @error('password')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label">
                        <span class="icon">▣</span>
                        <span>Confirm New Password</span>
                    </label>

                    <div class="field-wrap">
                        <input
                            type="password"
                            name="password_confirmation"
                            class="form-control"
                            placeholder="Confirm new password"
                        >
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="save-btn">Save Profile</button>
            </div>
        </form>
    </div>
@endsection