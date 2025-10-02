<x-app-layout>  
    @push('styles')
        <style>
            body { background-color: #f8f9fa; }
            .voting-card { max-width: 600px; margin: 2rem auto; }
            .voting-header { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; }
            .success-icon { font-size: 4rem; color: #28a745; }
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
                        <div class="success-icon mb-3">✓</div>
                        <h4 class="text-success">Vote Submitted Successfully!</h4>
                        <p class="text-muted">Thank you for participating in this voting event.</p>
                    </div>
                    
                    <div class="alert alert-success">
                        <strong>Your Selection:</strong><br>
                        <span class="h5">{{ $selectedOption->option_text }}</span>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout> 