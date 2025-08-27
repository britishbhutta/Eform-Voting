<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vote - {{ $votingEvent->title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .voting-card { max-width: 600px; margin: 2rem auto; }
        .option-card { cursor: pointer; transition: all 0.3s ease; }
        .option-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .option-card.selected { border-color: #0d6efd; background-color: #f8f9ff; }
        .voting-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="voting-card">
            <div class="card voting-header">
                <div class="card-body text-center">
                    <h2 class="card-title mb-2">{{ $votingEvent->title }}</h2>
                    <p class="card-text mb-0">{{ $votingEvent->question }}</p>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h5 class="card-title mb-3">Select your option:</h5>
                    
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    <form id="votingForm" method="POST" action="{{ route('voting.submit', ['token' => $votingEvent->token]) }}">
                        @csrf
                        <div class="row">
                            @foreach($votingEvent->options as $option)
                                <div class="col-md-6 mb-3">
                                    <div class="card option-card h-100" data-option-id="{{ $option->id }}">
                                        <div class="card-body text-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="selected_option" 
                                                       id="option_{{ $option->id }}" value="{{ $option->id }}" required>
                                                <label class="form-check-label" for="option_{{ $option->id }}">
                                                    {{ $option->option_text }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @error('selected_option')
                            <div class="alert alert-danger mt-3">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg px-5" id="submitBtn">
                                Submit Vote
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body text-center text-muted">
                    <small>
                        <strong>Voting Period:</strong><br>
                        @if($votingEvent->start_at)
                            Start: {{ \Carbon\Carbon::parse($votingEvent->start_at)->format('M d, Y H:i') }}
                        @endif
                        @if($votingEvent->end_at)
                            <br>End: {{ \Carbon\Carbon::parse($votingEvent->end_at)->format('M d, Y H:i') }}
                        @endif
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const optionCards = document.querySelectorAll('.option-card');
            const form = document.getElementById('votingForm');
            const submitBtn = document.getElementById('submitBtn');

            // Handle option selection
            optionCards.forEach(card => {
                card.addEventListener('click', function() {
                    const radio = this.querySelector('input[type="radio"]');
                    radio.checked = true;
                    
                    // Update visual selection
                    optionCards.forEach(c => c.classList.remove('selected'));
                    this.classList.add('selected');
                });
            });

            // Handle form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const selectedOption = document.querySelector('input[name="selected_option"]:checked');
                if (!selectedOption) {
                    alert('Please select an option before submitting.');
                    return;
                }

                // Disable submit button to prevent double submission
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';

                // Submit the form
                this.submit();
            });
        });
    </script>
</body>
</html>
