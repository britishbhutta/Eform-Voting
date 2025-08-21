<x-guest-layout>
    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
    @endpush

    <div class="container form-card">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="mb-3">Verify your email</h4>

                @if(session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                <p class="small text-muted">
                    We sent a 6-digit verification code to your email. Enter it here to activate your account.
                </p>

                <form method="POST" action="{{ route('verification.verify') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $email ?? '') }}" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="code" class="form-label">Verification Code</label>
                        <input id="code" name="code" type="text" value="{{ old('code') }}" class="form-control" required>
                        @error('code') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-grid mb-2">
                        <button type="submit" class="btn btn-primary">Verify</button>
                    </div>
                </form>

                <form method="POST" action="{{ route('verification.resend') }}">
                    @csrf
                    <input type="hidden" name="email" value="{{ old('email', $email ?? '') }}">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-outline-secondary">Resend code</button>
                    </div>
                </form>

                <div class="mt-3 small text-muted">
                    Did not receive the email? Check spam or try resending.
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
