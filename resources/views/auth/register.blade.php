{{-- resources/views/auth/register.blade.php --}}
<x-guest-layout>
    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
    @endpush

    @php
        // fallback variables
        $countries = $countries ?? collect();
        $selectedRole = $selectedRole ?? request('role') ?? old('role') ?? 'voter';

        // If somehow numeric role gets passed, normalize to string for display
        if (is_numeric($selectedRole)) {
            $selectedRole = ((int)$selectedRole === \App\Models\User::ROLE_CREATOR) ? 'creator' : 'voter';
        }
    @endphp
        
    <div class="container form-card form-compact">
        <div class="text-center mb-3">
            <h1 class="fw-bold">Sign up</h1>
            <div class="small text-muted">Signing up as <strong>{{ ucfirst($selectedRole) }}</strong></div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mb-3">
                    <button type="button" class="social-btn w-100 w-md-auto">&nbsp; Continue with Apple</button>

                    <!-- Google: preserve role in query -->
                    <a href="{{ route('google.login', ['role' => $selectedRole]) }}" class="social-btn btn-google w-100 w-md-auto" aria-label="Continue with Google">
                        <img src="https://www.svgrepo.com/show/355037/google.svg" alt="G" class="btn-google-icon">
                        Continue with Google
                    </a>
                </div>

                <div class="divider mb-3">
                    <hr>
                    <div class="small">or</div>
                    <hr>
                </div>

                <form method="POST" action="{{ route('register') }}" novalidate>
                    @csrf

                    {{-- preserve role in a hidden field (string) --}}
                    <input type="hidden" name="role" value="{{ old('role', $selectedRole) }}">

                    <div class="row mb-2">
                        <!-- First Name -->
                        <div class="col-md-6 mb-2 mb-md-0">
                            <label class="form-label" for="first_name">First Name</label>
                            <input id="first_name" name="first_name" type="text"
                                   class="form-control form-control-sm @error('first_name') is-invalid @enderror"
                                   value="{{ old('first_name') }}" required autofocus>
                            @error('first_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Last Name -->
                        <div class="col-md-6">
                            <label class="form-label" for="last_name">Last Name</label>
                            <input id="last_name" name="last_name" type="text"
                                   class="form-control form-control-sm @error('last_name') is-invalid @enderror"
                                   value="{{ old('last_name') }}" required>
                            @error('last_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label" for="email">Email</label>
                        <input id="email" name="email" type="email"
                               class="form-control form-control-sm @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-2 position-relative">
                        <label class="form-label" for="password">Password</label>
                        <div class="password-wrapper">
                            <input id="password" name="password" type="password"
                                   class="form-control form-control-sm with-toggle @error('password') is-invalid @enderror"
                                   placeholder="Password (8 or more characters)" required autocomplete="new-password">
                            <button type="button" id="togglePassword" class="password-toggle" tabindex="-1" aria-label="Toggle password visibility">
                                <!-- eye icon -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-2">
                        <label class="form-label" for="password_confirmation">Confirm Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" class="form-control form-control-sm" required>
                    </div>

                    <div class="mb-2">
                        <label class="form-label" for="country_id">Country</label>
                        <select id="country_id" name="country_id"
                                class="form-select form-select-sm @error('country_id') is-invalid @enderror"
                                required>
                            <option value="">{{ $countries->isEmpty() ? 'No countries available' : 'Select Country' }}</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                    {{ $country->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('country_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="text-center mt-5 mb-2">
                        <button type="submit" class="btn-create">Create my account</button>
                    </div>

                    <div class="text-center">
                        <small>Already have an account? <a href="{{ route('login') }}">Log In</a></small>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            (function(){
                const toggle = document.getElementById('togglePassword');
                const pw = document.getElementById('password');
                if (toggle && pw) {
                    toggle.addEventListener('click', function(){
                        if (pw.type === 'password') {
                            pw.type = 'text';
                            this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M13.359 11.238C14.31 10.1 15 8.6 15 8s-.69-2.1-1.641-3.238C12.646 3.25 10.429 1.5 8 1.5 6.994 1.5 5.993 1.69 5.08 2.05L9.88 6.85A2.5 2.5 0 0 1 13.359 11.238zM3.707 2.293 2.293 3.707l2.158 2.158C3.887 7.188 3 8.56 3 8c0 0 2.5 4.5 5 4.5.878 0 1.713-.186 2.463-.513L13.707 13.707 15.121 12.293 3.707 2.293z"/></svg>';
                        } else {
                            pw.type = 'password';
                            this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8z"/><path d="M8 5.5A2.5 2.5 0 1 0 8 10.5 2.5 2.5 0 1 0 8 5.5z"/></svg>';
                        }
                    });
                }
            })();
        </script>
    @endpush
</x-guest-layout>
