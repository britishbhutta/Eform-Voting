

 
{{-- resources/views/partials/tariff-cards.blade.php --}}
 
<div class="row g-2 justify-content-center wizard-cards">
    @forelse($tariffs as $tariff)
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            @if($booking->payment_status === "succeeded")
                @if($booking->tariff_id === $tariff->id)
                    <div class="card tariff-card tariff-card-compact selectable-card {{ (!empty($selectedTariff) && $selectedTariff->id === $tariff->id) ? 'selected' : '' }}" data-tariff-id="{{ $tariff->id }}" tabindex="0" role="button" aria-pressed="{{ (!empty($selectedTariff) && $selectedTariff->id === $tariff->id) ? 'true' : 'false' }}">
                @else
                    <div class="card tariff-card tariff-card-compact {{ (!empty($selectedTariff) && $selectedTariff->id === $tariff->id) ? 'selected' : '' }}" data-tariff-id="{{ $tariff->id }}" tabindex="0" role="button" aria-pressed="{{ (!empty($selectedTariff) && $selectedTariff->id === $tariff->id) ? 'true' : 'false' }}">
                @endif
            @else
                    <div class="card tariff-card tariff-card-compact selectable-card {{ (!empty($selectedTariff) && $selectedTariff->id === $tariff->id) ? 'selected' : '' }}" data-tariff-id="{{ $tariff->id }}" tabindex="0" role="button" aria-pressed="{{ (!empty($selectedTariff) && $selectedTariff->id === $tariff->id) ? 'true' : 'false' }}">
            @endif
                <div class="card-header text-center">
                    <strong>{{ $tariff->title }}</strong>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="tariff-range mb-2">{{ $tariff->description }}</p>
                    <p class="tariff-note text-muted small mb-3">{{ $tariff->note }}</p>
                    <div class="price-wrapper text-center mb-2">
                        <div class="price"><strong>{{ number_format($tariff->price_cents / 100, 2) }} {{ $tariff->currency }}</strong></div>
                        <div class="price-underline"></div>
                    </div>
                    <ul class="list-unstyled mb-3 tariff-features">
                        @foreach((array) json_decode($tariff->features ?? '[]', true) as $feature)
                            <li>✓ {{ $feature }}</li>
                        @endforeach
                    </ul>
                    <div class="mt-auto text-center">
                        <button type="button" class="btn btn-outline-dark select-btn" aria-label="Select {{ $tariff->title }}">Select</button>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info">No tariff plans available at the moment.</div>
        </div>
    @endforelse

    
</div>
 
 