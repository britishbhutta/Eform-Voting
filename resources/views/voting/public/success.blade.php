<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vote Submitted - {{ $votingEvent->title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .voting-card { max-width: 600px; margin: 2rem auto; }
        .voting-header { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; }
        .success-icon { font-size: 4rem; color: #28a745; }
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
</body>
</html>
