{{-- resources/views/voting/step.blade.php --}}
<x-app-layout>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    @endpush

    <div class="container py-4">
        @include('partials.voting-tabs')

        {{-- Progress bar --}}
        <div class="wizard-progress-wrapper mb-4">
            <div class="progress-labels d-flex justify-content-between align-items-start">
                @foreach($stepNames as $num => $name)
                    <a @if($num <= $currentStep) href="{{ route('voting.create.step', ['step' => $num] + (request()->query() ? request()->query() : [])) }}" @endif
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

        {{-- Step content --}}
        <div class="card">
            <div class="card-body">
                @if($currentStep > 1 && empty($selectedTariff))
                    <div class="alert alert-warning mb-4">
                        Please select a tariff first from Step 1 before proceeding.
                    </div>
                    <div class="d-flex justify-content-start">
                        <a href="{{ route('voting.create.step', ['step' => 1]) }}" class="btn btn-light">Go back to Step 1</a>
                    </div>
                @else
                    @if($currentStep === 1)
                        {{-- STEP 1: Choose tariff (via partial) --}}
                        @include('partials.tariff-cards', ['tariffs' => $tariffs, 'selectedTariff' => $selectedTariff ?? null])
                    @elseif($currentStep === 2)
                        {{-- STEP 3: Insert reward --}}
                        @if($booking)
                            @include('partials.payment-successfull')
                        @else
                            @include('partials.personal-info-payment', ['selectedTariff' => $selectedTariff ?? null, 'currentStep' => $currentStep])
                        @endif

                    @elseif($currentStep === 3)
                        {{-- STEP 3: Insert reward --}}
                        @include('partials.reward-form', ['selectedTariff' => $selectedTariff ?? null])
                    @elseif($currentStep === 4)
                        {{-- STEP 4: Detail of event --}}
                        
                        @include('partials.details-form', ['selectedTariff' => $selectedTariff ?? null])
                    @else

                        @include('partials.qr-code', ['booking' => $booking ?? null])

                        
                        {{-- Wizard actions area --}}
                        <div class="d-flex justify-content-between mt-4">
                            {{-- Back link --}}
                            @php
                                $prev = $currentStep - 1;
                                $prevUrl = $prev >= 1 ? route('voting.create.step', ['step' => $prev]) : route('voting.realized');
                            @endphp
                            <a href="{{ $prevUrl }}" class="btn btn-light">{{ $prev >= 1 ? 'Back' : 'Cancel' }}</a>
                            <a href="{{ route('voting.create.step', ['step' => 1] + (request()->query() ? request()->query() : [])) }}" class="btn btn-primary">Finish</a>
                        </div>
                    @endif
                @endif
            </div>
        </div>

         {{-- For step 1 actions (outside card, wrapped in form) --}}
        @if($currentStep === 1)
            <form method="POST" action="{{ route('voting.select_tariff') }}">
                @csrf
                <input type="hidden" name="tariff" id="selectedTariffInput" value="{{ $selectedTariff ? $selectedTariff->id : '' }}">
 
                <div class="wizard-actions mt-4 d-flex justify-content-between align-items-center">
                    <div class="selected-info text-muted">
                        @if(!empty($selectedTariff))
                            Selected: {{ $selectedTariff->title }}
                        @else
                            No tariff selected
                        @endif
                    </div>
                    <div>
                        <a href="{{ route('voting.realized') }}" class="btn btn-light me-2">Cancel</a>
                        <button type="submit" id="wizardNextBtn" class="btn btn-success" {{ empty($selectedTariff) ? 'disabled' : '' }}>Next</button>
                    </div>
                </div>
            </form>
        @endif
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const currentStep = Number(@json($currentStep));
            document.querySelectorAll('.progress-node').forEach(function (node) {
                const step = Number(node.getAttribute('data-step'));
                if (step <= currentStep) {
                    node.classList.add('active');
                } else {
                    node.classList.remove('active');
                }
            });
 
            if (currentStep === 1) {
                const cards = document.querySelectorAll('.selectable-card');
                const nextBtn = document.getElementById('wizardNextBtn');
                const selectedInfo = document.querySelector('.selected-info');
                const selectedInput = document.getElementById('selectedTariffInput');
                let selectedTariffId = @json($selectedTariff ? $selectedTariff->id : '');
 
                function setSelected(cardEl) {
                    cards.forEach(c => c.classList.remove('selected'));
                    cardEl.classList.add('selected');
                    selectedTariffId = cardEl.getAttribute('data-tariff-id');
                    const header = cardEl.querySelector('.card-header strong');
                    const title = header ? header.innerText : 'Tariff';
                    selectedInfo.textContent = 'Selected: ' + title;
                    selectedInput.value = selectedTariffId;
                    if (nextBtn) nextBtn.disabled = false;
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
                    const btn = card.querySelector('.select-btn');
                    if (btn) {
                        btn.addEventListener('click', function (ev) {
                            ev.stopPropagation();
                            setSelected(card);
                        });
                    }
                });
 
                if (selectedTariffId) {
                    const initialCard = [...cards].find(c => c.getAttribute('data-tariff-id') === selectedTariffId.toString());
                    if (initialCard) {
                        setSelected(initialCard);
                    }
                }
            }
        });
    </script>
    @endpush
</x-app-layout>