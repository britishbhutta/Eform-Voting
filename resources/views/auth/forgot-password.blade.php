<x-guest-layout>
    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>.form-card{max-width:420px;margin:0 auto;} .form-card .card-body{padding:20px;}</style>
        <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    @endpush

    <div class="container form-card">
        @if (session('status'))
            <div class="alert alert-success" role="alert">{{ session('status') }}</div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-md-5">
                <h1 class="h4 mb-3">{{ __('Forgot your password?') }}</h1>
                <p class="text-muted">{{ __('No problem. Enter your email and we will email you a password reset link.') }}</p>

                <form method="POST" action="{{ route('password.email') }}" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label class="form-label" for="email">{{ __('Email') }}</label>
                        <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus>
                        @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-blue">{{ __('Email Password Reset Link') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @endpush
</x-guest-layout>
