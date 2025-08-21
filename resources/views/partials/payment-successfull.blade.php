@php
    // Ensure variable exists
    $selectedTariff = $selectedTariff ?? null;
@endphp

 @if($selectedTariff)
        <div class="mb-3">
            <div class="alert alert-light">
                <strong>Selected tariff:</strong>
                {{ $selectedTariff->title }}
                — {{ number_format($selectedTariff->price_cents / 100, 2) }} {{ $selectedTariff->currency }}
            </div>
        </div>
    @endif

<div class="container mt-3">
    <div id="payment-success" class="text-center ">
        <div class="d-flex justify-content-center align-items-center flex-column">
            <div class="rounded-circle bg-success d-flex justify-content-center align-items-center"
                style="width:120px; height:120px;">
                <i class="bi bi-check-lg text-white" style="font-size:60px;"></i>
            </div>
            <h2 class="mt-4 text-success">Payment Received</h2>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between mt-4">
    @php
        $prev = ($currentStep ?? 2) - 1;
        $prevUrl = $prev >= 1 ? route('voting.create.step', ['step' => $prev]) : route('voting.realized');
 
        $nextStep = ($currentStep ?? 2) + 1;
        $nextBase = route('voting.create.step', ['step' => $nextStep]);
        $qs = request()->getQueryString();
        $nextUrl = $nextBase . ($qs ? ('?' . $qs) : '');
    @endphp

    <a href="{{ $prevUrl }}" class="btn btn-light">{{ $prev >= 1 ? 'Back' : 'Cancel' }}</a>

    {{-- Changed type to button so it doesn't submit --}}
    <button type="button" id="rewardNextBtn" class="btn btn-success" disabled>Next</button>
</div>

