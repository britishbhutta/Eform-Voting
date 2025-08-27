<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting Not Started - {{ $votingEvent->title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .voting-card { max-width: 600px; margin: 2rem auto; }
        .voting-header { background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); color: white; }
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
                        <i class="fas fa-clock fa-3x text-warning mb-3"></i>
                        <h4>Voting Hasn't Started Yet</h4>
                        <p class="text-muted">This voting event will begin on:</p>
                        <h5 class="text-primary">{{ \Carbon\Carbon::parse($votingEvent->start_at)->format('M d, Y \a\t H:i') }}</h5>
                    </div>
                    
                    <div class="alert alert-info">
                        <strong>Voting Period:</strong><br>
                        Start: {{ \Carbon\Carbon::parse($votingEvent->start_at)->format('M d, Y H:i') }}<br>
                        @if($votingEvent->end_at)
                            End: {{ \Carbon\Carbon::parse($votingEvent->end_at)->format('M d, Y H:i') }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
