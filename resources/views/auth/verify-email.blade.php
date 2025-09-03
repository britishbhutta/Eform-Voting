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

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error)
                            {{ $error }}
                        @endforeach
                    </div>
                @endif

                <p class="small text-muted">
                    We sent a 6-digit verification code to your email. Enter it here to activate your account.
                    <br><strong>The code will expire in 2 minutes.</strong>
                </p>

                <form method="POST" action="{{ route('verification.verify') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $email ?? session('email_for_verification', '')) }}" class="form-control @error('email') is-invalid @enderror" readonly required>
                        @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
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
                    <input type="hidden" name="email" value="{{ old('email', $email ?? session('email_for_verification', '')) }}">
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

    @push('scripts')
    <script>
        // Ensure email is maintained in session storage as backup
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('email');
            const emailValue = emailInput.value;
            
            if (emailValue) {
                // Store email in sessionStorage as backup
                sessionStorage.setItem('verification_email', emailValue);
            } else {
                // Try to get email from sessionStorage if not in input
                const storedEmail = sessionStorage.getItem('verification_email');
                if (storedEmail) {
                    emailInput.value = storedEmail;
                    // Update hidden input in resend form
                    const hiddenEmailInput = document.querySelector('input[name="email"][type="hidden"]');
                    if (hiddenEmailInput) {
                        hiddenEmailInput.value = storedEmail;
                    }
                }
            }
        });
    </script>
    @endpush
</x-guest-layout>
