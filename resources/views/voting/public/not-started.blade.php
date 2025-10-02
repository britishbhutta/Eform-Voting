<x-app-layout>  
    @push('styles')
        <style>
            body { background-color: #f8f9fa; }
            .voting-card { max-width: 600px; margin: 2rem auto; }
            .voting-header { background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); color: white; }
            .countdown-timer { font-size: 1.5rem; font-weight: bold; color: #007bff; }
            .countdown-expired { color: #28a745; }
        </style>
    @endpush
        <div class="container">
            <div class="text-center">
                <span class="fw-bold fs-5">Voting Form - Reward</span>
                <span class="fw-bold fs-5">" {{ $reward->name }} "</span>
            </div>
            <div class="voting-card">
                <div class="card voting-header">
                    <div class="card-body text-center">
                        <h2 class="card-title mb-2">{{ $votingEvent->title }}</h2>
                        <p class="card-text mb-0">{{ $votingEvent->question }}</p>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <i class="fas fa-clock fa-3x text-warning mb-3"></i>
                            <h4>Voting Hasn't Started Yet</h4>
                            <p class="text-muted">This voting event will begin on:</p>
                            <h5 class="text-primary">{{ \Carbon\Carbon::parse($votingEvent->start_at)->setTimezone($timezone ?? config('app.timezone'))->format('M d, Y \a\t H:i T') }}</h5>
                            
                            <div class="mt-3">
                                <p class="text-muted mb-2">Time remaining:</p>
                                <div id="countdown" class="countdown-timer">
                                    Calculating...
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <strong>Voting Period:</strong><br>
                            Start: {{ \Carbon\Carbon::parse($votingEvent->start_at)->setTimezone($timezone ?? config('app.timezone'))->format('M d, Y H:i T') }}<br>
                            @if($votingEvent->end_at)
                                End: {{ \Carbon\Carbon::parse($votingEvent->end_at)->setTimezone($timezone ?? config('app.timezone'))->format('M d, Y H:i T') }}
                            @endif
                        </div>
                        @if(!Auth::check())
                            <div class="mt-4 text-center mb-4">
                                <a href="{{ route('login') }}" class="fw-bold text-decoration-none text-primary">
                                    Join The Voting
                                    &nbsp; | &nbsp;
                                    Log In or Register
                                </a>
                            </div>
                        @endif
                        <div class="mt-3">
                            <small class="text-muted">This page will automatically redirect when voting starts.</small>
                            <br><small class="text-muted">Timezone: {{ $timezone ?? config('app.timezone') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @push('scripts')
        <script>

            const startTimeISO = '{{ $votingEvent->start_at->setTimezone($timezone ?? config("app.timezone"))->toISOString() }}';
            const startTime = new Date(startTimeISO).getTime();
            const countdownElement = document.getElementById('countdown');
            const eventTimezone = '{{ $timezone ?? config("app.timezone") }}';
            
            function updateCountdown() {
                const now = new Date().getTime();
                const distance = startTime - now;
                
                if (distance < 0) {
                    countdownElement.innerHTML = '<span class="countdown-expired">Voting has started! Redirecting...</span>';
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                    return;
                }
                
        
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
            
                let countdownText = '';
                if (days > 0) {
                    countdownText += days + 'd ';
                }
                if (hours > 0 || days > 0) {
                    countdownText += hours + 'h ';
                }
                if (minutes > 0 || hours > 0 || days > 0) {
                    countdownText += minutes + 'm ';
                }
                countdownText += seconds + 's';
                
                countdownElement.innerHTML = countdownText;
                
                
                if (distance < 120000 && !window.frequentCheckStarted) {
                    window.frequentCheckStarted = true;
                    const frequentCheck = setInterval(() => {
                        checkVotingStatus();
                    }, 5000);
                    
                    setTimeout(() => {
                        clearInterval(frequentCheck);
                    }, 300000);
                }
            }
            
            function checkVotingStatus() {
                fetch(`/api/voting/${token}/status`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'active') {
                            window.location.reload();
                        } else if (data.status === 'ended') {
                            window.location.reload();
                        }
                    })
                    .catch(() => {
                    });
            }

            const token = '{{ $votingEvent->token }}';
            updateCountdown();
            
            const countdownInterval = setInterval(updateCountdown, 1000);
            

            const checkInterval = setInterval(() => {
                checkVotingStatus();
            }, 30000);
            
            window.addEventListener('beforeunload', () => {
                clearInterval(countdownInterval);
                clearInterval(checkInterval);
            });
        </script>
    @endpush
</x-app-layout> 