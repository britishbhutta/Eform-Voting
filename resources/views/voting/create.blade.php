{{-- resources/views/voting/create.blade.php --}}
<x-app-layout>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    @endpush

    @php
        $currentStep = 1;
    @endphp

    <div class="container py-4">
        @include('partials.voting-tabs')

        {{-- Progress bar (using standardized version) --}}
        <div class="wizard-progress-wrapper mb-4">
            <div class="progress-labels d-flex justify-content-between align-items-start">
                @foreach($stepNames as $num => $name)
                    <a href="{{ route('voting.create.step', ['step' => $num] + (request()->query() ? request()->query() : [])) }}"
                       class="progress-node text-center {{ $num <= $currentStep ? 'active' : '' }}"
                       data-step="{{ $num }}">
                        <div class="node-number">{{ $num }}</div>
                        <div class="node-name">{{ $name }}</div>
                    </a>
                @endforeach
            </div>
            <div class="progress-bar-line" aria-hidden="true"></div>
            <div class="progress-active-line" style="width: {{ (($currentStep - 1) / (count($stepNames) - 1)) * 88 }}%;"></div>
        </div>

        {{-- Tariff cards (via partial) --}}
        @include('partials.tariff-cards')

        {{-- Bottom action area --}}
        <div class="wizard-actions mt-4 d-flex justify-content-between align-items-center">
            <div class="selected-info text-muted">No tariff selected</div>
            <div>
                <a href="{{ route('voting.realized') }}" class="btn btn-light me-2">Cancel</a>
                <button id="wizardNextBtn" class="btn btn-success" disabled>Next</button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        (function () {
            const cards = document.querySelectorAll('.selectable-card');
            const nextBtn = document.getElementById('wizardNextBtn');
            const selectedInfo = document.querySelector('.selected-info');
            let selectedTariff = null;

            function setSelected(cardEl) {
                cards.forEach(c => c.classList.remove('selected'));
                cardEl.classList.add('selected');
                selectedTariff = cardEl.getAttribute('data-tariff-id');
                const title = cardEl.querySelector('.card-header strong').innerText;
                selectedInfo.textContent = 'Selected: ' + title;
                nextBtn.removeAttribute('disabled');
            }

            cards.forEach(card => {
                card.addEventListener('click', function () {
                    setSelected(this);
                });
                card.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        setSelected(this);
                    }
                });
                const selBtn = card.querySelector('.select-btn');
                if (selBtn) {
                    selBtn.addEventListener('click', (ev) => {
                        ev.stopPropagation();
                        setSelected(card);
                    });
                }
            });

            nextBtn.addEventListener('click', function () {
                if (!selectedTariff) return;
                const url = new URL("{{ route('voting.create.step', ['step' => 2]) }}", window.location.origin);
                url.search = window.location.search;
                url.searchParams.set('tariff', selectedTariff);
                window.location.href = url.toString();
            });
        })();
    </script>
    @endpush
</x-app-layout>