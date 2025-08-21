{{-- resources/views/auth/choose-role.blade.php --}}
<x-guest-layout>
    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="{{ asset('css/choose-role.css') }}" rel="stylesheet">
    @endpush

    @php
        // role names used by your app: 'voter' and 'creator'
        $firstRole = 'voter';
        $secondRole = 'creator';
        $firstTitle = "I'm a Voter";
        $firstSubtitle = "/for example, a radio listener, televsion viewer or a fan of a sporting event../";
        $secondTitle = "I'm a Creator";
        $secondSubtitle = "of Voting Competition /for example, radio, television, sporting events, etc.../";
    @endphp

    <div class="choose-role-page d-flex align-items-center min-vh-100">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 text-center mb-4">
                    <h1 class="choose-role-title">Create an account</h1>
                    <p class="text-muted">Choose the account type that best describes you</p>
                </div>

                <div class="col-lg-8">
                    <div class="role-cards d-flex gap-3 justify-content-center flex-column flex-md-row">
                        {{-- Card 1 --}}
                        <label class="role-card selected" tabindex="0" data-role="{{ $firstRole }}">
                            <input type="radio" name="role" value="{{ $firstRole }}" checked class="d-none" />
                            <div class="role-card-inner">
                                <div class="role-icon"><!-- optional icon --><svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M12 12a5 5 0 100-10 5 5 0 000 10zM2 22s2-4 10-4 10 4 10 4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
                                <div class="role-text">
                                    <h5 class="role-title">{{ $firstTitle }}</h5>
                                    <p class="role-sub">{{ $firstSubtitle }}</p>
                                </div>
                                <div class="role-radio">
                                    <span class="radio-outer"><span class="radio-inner"></span></span>
                                </div>
                            </div>
                        </label>

                        {{-- Card 2 --}}
                        <label class="role-card" tabindex="0" data-role="{{ $secondRole }}">
                            <input type="radio" name="role" value="{{ $secondRole }}" class="d-none" />
                            <div class="role-card-inner">
                                <div class="role-icon"><svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M12 12a5 5 0 100-10 5 5 0 000 10zM2 22s2-4 10-4 10 4 10 4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
                                <div class="role-text">
                                    <h5 class="role-title">{{ $secondTitle }}</h5>
                                    <p class="role-sub">{{ $secondSubtitle }}</p>
                                </div>
                                <div class="role-radio">
                                    <span class="radio-outer"><span class="radio-inner"></span></span>
                                </div>
                            </div>
                        </label>
                    </div>

                    <div class="text-center mt-4">
                        {{-- Primary join button — JS will update href based on selection --}}
                        <a id="joinBtn" href="{{ route('register', ['role' => $firstRole]) }}" class="btn btn-success btn-join px-4 py-2">
                            Join as a {{ ucfirst($firstRole) }}
                        </a>
                    </div>
                    <br>
                    <div class="text-center">
                        <small>Already have an account? <a href="{{ route('login') }}">Log In</a></small>
                    </div>
                  
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        (function () {
            // Elements
            const cards = document.querySelectorAll('.role-card');
            const joinBtn = document.getElementById('joinBtn');
            const googleBtn = document.getElementById('googleBtn');
            const registerBase = "{{ route('register') }}";
            const googleBase = "{{ route('google.login') }}";

            function setActiveRole(role) {
                // Visual: mark selected
                cards.forEach(c => {
                    if (c.dataset.role === role) c.classList.add('selected');
                    else c.classList.remove('selected');
                    // set radio input
                    const input = c.querySelector('input[type="radio"]');
                    if (input) input.checked = (c.dataset.role === role);
                });

                // Update primary button href and label
                joinBtn.href = registerBase + '?role=' + encodeURIComponent(role);
                joinBtn.textContent = 'Join as a ' + (role === 'creator' ? 'Creator' : 'Voter');

                // Update google btn (preserve role)
                googleBtn.href = googleBase + '?role=' + encodeURIComponent(role);
            }

            // Wire up clicks & keyboard
            cards.forEach(c => {
                c.addEventListener('click', () => setActiveRole(c.dataset.role));
                c.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        setActiveRole(c.dataset.role);
                    }
                });
            });

            // initial state
            const initial = document.querySelector('.role-card.selected')?.dataset.role || 'voter';
            setActiveRole(initial);
        })();
    </script>
    @endpush
</x-guest-layout>
