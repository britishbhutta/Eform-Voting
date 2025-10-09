<x-guest-layout>
    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>.form-card{max-width:420px;margin:0 auto;} .form-card .card-body{padding:20px;}</style>
        <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    @endpush

    <div class="container form-card">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-md-5">
                <h1 class="h4 mb-3">{{ __('Confirm your password') }}</h1>
                <p class="text-muted">{{ __('This is a secure area of the application. Please confirm your password before continuing.') }}</p>

                <form method="POST" action="{{ route('password.confirm') }}" novalidate>
                    @csrf

                    <div class="mb-4">
                        <label class="form-label" for="password">{{ __('Password') }}</label>
                        <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password">
                        @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-blue">{{ __('Confirm') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @endpush
</x-guest-layout>
