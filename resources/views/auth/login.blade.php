<x-guest-layout>
    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Custom stylesheet (moved from inline styles) -->
        <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
    @endpush

    <div class="container form-card">
        @if (session('status'))
            <div class="alert alert-success" role="alert">{{ session('status') }}</div>
        @endif

        <div class="text-center mb-3">
            <h1 class="fw-bold">Log in</h1>
        </div>
        <!-- success messege -->
        {{-- show success/status messages --}}
        @if(session('status'))
          <div class="alert alert-success" role="alert">
            {{ session('status') }}
          </div>
        @endif

              @if(session('success'))
                <div class="alert alert-success" role="alert">
                  {{ session('success') }}
                </div>
              @endif

              @if(session('error'))
                <div class="alert alert-danger" role="alert">
                  {{ session('error') }}
                </div>
              @endif

              {{-- show validation errors --}}
              @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                  <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                      <li>{{ $error }}</li>
                    @endforeach
                  </ul>
                </div>
              @endif


        <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-md-5">
                <form method="POST" action="{{ route('login') }}" novalidate>
                    @csrf

                    <!-- Email -->
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input id="email" name="email" type="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required autofocus autocomplete="username">
                        @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-3 position-relative">
                        <div class="d-flex justify-content-between align-items-center">
                     <label class="form-label mb-0" for="password">Password</label>
                     </div>
                     <div class="password-wrapper">
                      <input id="password" name="password" type="password"
                         class="form-control @error('password') is-invalid @enderror"
                         required autocomplete="current-password">
                         <button type="button" id="toggleLoginPassword" class="password-toggle" tabindex="-1" aria-label="Toggle password visibility">
                        <!-- eye icon -->
                         <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" 
                          fill="none" stroke="currentColor" stroke-width="2" 
                          stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/>
                          <circle cx="12" cy="12" r="3"/>
                          </svg>
                          </button>
                             </div>

                          @error('password')
                          <div class="invalid-feedback d-block">{{ $message }}</div>
                           @enderror
                        </div>


                    <!-- hCaptcha -->
                    <div class="mt-4">
                        <div class="h-captcha" data-sitekey="{{ env('HCAPTCHA_SITEKEY') }}" data-size="normal"></div>
                        @error('h-captcha-response')
                        <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Remember + Forgot Password -->
                    <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="remember_me" name="remember">
                            <label class="form-check-label" for="remember_me">Remember me</label>
                        </div>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="small text-secondary link-underline-hover">Forgot password?</a>
                        @endif
                    </div>

                    <!-- Submit -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Log in</button>
                    </div>

                    <!-- OR divider -->
                    <div class="d-flex align-items-center my-3 text-muted">
                        <hr class="flex-grow-1">
                        <span class="px-2 small text-uppercase">or</span>
                        <hr class="flex-grow-1">
                    </div>

                    <!-- Social buttons -->
                    <div class="d-grid gap-2">
                       <!-- Apple button -->
                        <button type="button" class="social-btn w-100 w-md-auto">&nbsp; Continue with Apple</button>

                        {{-- google --}}

                        <a href="{{ route('google.login') }}"
                           class="btn-google social-btn w-100"
                           aria-label="Continue with Google">
                            <img src="https://www.svgrepo.com/show/355037/google.svg" alt="G" class="btn-google-icon">
                            Continue with Google
                        </a>
                    </div>
                </form>

                <!-- SIGNUP BLOCK: placed under form, matches page UI -->
                <div class="signup-block text-center mt-3">
                    <p class="small small-muted mb-2">Don't have an account?</p>

                    <!-- Primary CTA (outline but prominent) -->
                    <a href="{{ url('/') }}"
                       class="btn btn-outline-primary w-100 btn-create-account"
                       aria-label="Create an account">
                        Create an account
                    </a>
                </div>

            </div>
        </div>
    </div>

    <script>
    document.getElementById('toggleLoginPassword').addEventListener('click', function () {
        const passwordInput = document.getElementById('password');
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
    });
    </script>


    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://hcaptcha.com/1/api.js" async defer></script>
    @endpush
</x-guest-layout>



