<x-app-layout> 
    @push('styles')
        <style>
            body { background-color: #f8f9fa; }
            .voting-card { max-width: 600px; margin: 2rem auto; }
            .voting-header { background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: white; }
        </style>
    @endpush
    <div class="container">
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
                        <i class="fas fa-calendar-times fa-3x text-secondary mb-3"></i>
                        <h4>Voting Has Ended</h4>
                        <p class="text-muted">This voting event ended on:</p>
                        <h5 class="text-secondary">{{ \Carbon\Carbon::parse($votingEvent->end_at)->format('M d, Y \a\t H:i') }}</h5>
                    </div>
                    @if(!Auth::check())
                        <div class="mt-4 text-center mb-4">
                            <span class="fw-bold fs-5">Join The Voting</span>
                            &nbsp; | &nbsp;
                            <a href="{{ route('login') }}" class="fw-bold text-decoration-none text-primary">
                                Log In or Register
                            </a>
                        </div>
                    @endif
                    <div class="alert alert-secondary">
                        <strong>Voting Period:</strong><br>
                        @if($votingEvent->start_at)
                            Start: {{ \Carbon\Carbon::parse($votingEvent->start_at)->format('M d, Y H:i') }}<br>
                        @endif
                        End: {{ \Carbon\Carbon::parse($votingEvent->end_at)->format('M d, Y H:i') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 

