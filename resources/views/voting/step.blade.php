{{-- resources/views/voting/step.blade.php --}}
<x-app-layout>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
        @if(session('error'))
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                Toastify({
                    text: "{{ session('error') }}",
                    duration: 3000, // 5 seconds
                    gravity: "top", // `top` or `bottom`
                    position: "right", // `left`, `center` or `right`
                    backgroundColor: "linear-gradient(to right, #ff5f6d, #ffc371)", // red/orange for error
                    stopOnFocus: true, // pause on hover
                    close: true
                }).showToast();
            });
        </script>
    @endif
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
                @if($currentStep > 1 && empty($selectedTariff) && !session()->has('booking_id'))
                    <div class="alert alert-warning mb-4">
                        Please Start From Step 1 For Proceeding.
                    </div>
                    <div class="d-flex justify-content-start">
                        <a href="{{ route('voting.create.step', ['step' => 1]) }}" class="btn btn-light">Go back to Step 1</a>
                    </div>
                @else
                    @if($currentStep === 1)
                        @include('partials.personal-info')
                    @elseif($currentStep === 2)
                    
                        @if($booking->payment_status == 'succeeded')
                            <div class="alert alert-warning" role="alert">
                                Tariff can not be changed Because Payment Has Been Received for this Tariff. 
                            </div>
                        @endif
                    
                        <div class="selected-info-tariff text-muted mb-4">
                            @if(!empty($selectedTariff))

                            @else
                                No tariff selected
                            @endif
                            
                        </div>
                        {{-- STEP 2: Choose tariff (via partial) --}}
                        @include('partials.tariff-cards', ['tariffs' => $tariffs, 'selectedTariff' => $selectedTariff ?? null])
                        <form method="POST" action="{{ route('voting.select_tariff') }}">
                        @csrf
                        <input type="hidden" name="tariff" id="selectedTariffInput" value="{{ $selectedTariff ? $selectedTariff->id : '' }}">

                        <div class="wizard-actions mt-4 d-flex justify-content-between align-items-center">
                            <div class="selected-info text-muted">
                                @if(!empty($selectedTariff))
                                    <!-- Selected: {{ $selectedTariff->title }} -->
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="termsCheckbox">
                                        <label class="form-check-label" for="termsCheckbox">
                                            I agree to the <a href="" target="_blank">Terms & Conditions</a>
                                        </label>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between mt-4">
                                @php
                                    $prev = ($currentStep ?? 3) - 1;
                                    $prevUrl = $prev >= 1 ? route('voting.create.step', ['step' => $prev]) : route('voting.realized');
                                @endphp

                                <a href="{{ $prevUrl }}" class="btn btn-light">{{ $prev >= 1 ? 'Back' : 'Cancel' }}</a>
                                <button type="submit" id="wizardNextBtn" class="btn btn-success" {{ empty($selectedTariff) ? 'disabled' : '' }}>Next</button>
                            </div>
                        </div>
                    </form>

                    @elseif($currentStep === 3)
                        {{-- STEP 4: Insert reward --}}
                        @include('partials.reward-form', ['selectedTariff' => $selectedTariff ?? null])
                    @elseif($currentStep === 4)
                        @include('partials.details-form', ['selectedTariff' => $selectedTariff ?? null])
                    @elseif($currentStep === 5)
                        {{-- STEP 4: Insert reward --}}
                        @if($booking->payment_status == 'succeeded')
                            @include('partials.payment-successfull')
                        @else
                            @if(is_null($booking->tariff_id))
                                <div class="alert alert-warning mb-4">
                                    Please Select Tariff From Step 2 For Proceeding.
                                </div>
                                <div class="d-flex justify-content-start">
                                    <a href="{{ route('voting.create.step', ['step' => 2]) }}" class="btn btn-light">Go back to Step 2</a>
                                </div>
                            @else
                                @include('partials.payment', ['selectedTariff' => $selectedTariff ?? null, 'currentStep' => $currentStep])
                            @endif
                        @endif
                    @else

                        @include('partials.qr-code', ['booking' => $booking ?? null])

                        @if(session('complete_errors'))
                            <div class="alert alert-danger mt-3">
                                <div class="fw-bold mb-2">Please resolve the following before finishing:</div>
                                <ul class="mb-0">
                                    @foreach((array) session('complete_errors') as $msg)
                                        <li>{{ $msg }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        
                        {{-- Wizard actions area --}}
                        <div class="d-flex justify-content-between mt-4">
                            {{-- Back link --}}
                            @php
                                $prev = $currentStep - 1;
                                $prevUrl = $prev >= 1 ? route('voting.create.step', ['step' => $prev]) : route('voting.realized');
                            @endphp
                            <a href="{{ $prevUrl }}" class="btn btn-light">{{ $prev >= 1 ? 'Back' : 'Cancel' }}</a>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#finishOverviewModal">Finish</button>
                        </div>
                    @endif
                @endif
            </div>
        </div>

         {{-- For step 2 actions (outside card, wrapped in form) --}}
        @if($currentStep === 2)
            
        @endif
    </div>

    @if($currentStep === 6)
        <div class="modal fade" id="finishOverviewModal" tabindex="-1" aria-labelledby="finishOverviewLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="finishOverviewLabel">Review your voting setup</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <h6>1) Tariff</h6>
                            <div class="text-muted">{{ $selectedTariff?->title ?? '-' }} — {{ $selectedTariff ? number_format($selectedTariff->price_cents/100,2) . ' ' . $selectedTariff->currency : '' }}</div>
                        </div>
                        <div class="mb-3">
                            <h6>2) Billing / Booking</h6>
                            @if(!empty($booking))
                                <div class="small text-muted">
                                    <div><strong>Name:</strong> {{ $booking->name ?? '-' }}</div>
                                    <div><strong>Email:</strong> {{ $booking->email ?? '-' }}</div>
                                    <div><strong>Address:</strong> {{ $booking->address ?? '-' }}, {{ $booking->city ?? '-' }} {{ $booking->zip ?? '' }}</div>
                                    <div><strong>Country:</strong> {{ $booking->country ?? '-' }}</div>
                                    <div><strong>Reference:</strong> {{ $booking->booking_reference ?? '-' }}</div>
                                    <div><strong>Payment:</strong> {{ ucfirst($booking->payment_method ?? '-') }} ({{ $booking->payment_status ?? '-' }})</div>
                                </div>
                            @else
                                <div class="text-danger">No booking found.</div>
                            @endif
                        </div>
                        <div class="mb-3">
                            <h6>3) Reward</h6>
                            @php $reward = $booking?->reward; @endphp
                            <div class="small text-muted">
                                <div><strong>Name:</strong> {{ $reward->name ?? '-' }}</div>
                                <div><strong>Description:</strong> {{ $reward->description ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h6>4) Voting Event</h6>
                            @php $overviewEvent = isset($votingEvent) ? $votingEvent : (isset($booking) ? \App\Models\VotingEvent::with('options')->where('booking_id', $booking->id)->first() : null); @endphp
                            <div class="small text-muted">
                                <div><strong>Title:</strong> {{ $overviewEvent?->title ?? '-' }}</div>
                                <div><strong>Question:</strong> {{ $overviewEvent?->question ?? '-' }}</div>


                                <div><strong>Start:</strong> {{ $overviewEvent?->start_at ? \Carbon\Carbon::parse($overviewEvent->start_at)->setTimezone($timezone ?? config('app.timezone'))->format('M d, Y H:i T') : '-' }} {{ $localTime }}</div>
                                <div><strong>End:</strong> {{ $overviewEvent?->end_at ? \Carbon\Carbon::parse($overviewEvent->end_at)->setTimezone($timezone ?? config('app.timezone'))->format('M d, Y H:i T') : '-' }} {{ $localTime }}</div>

                                <div><strong>Options:</strong>
                                    @if($overviewEvent && $overviewEvent->options->isNotEmpty())
                                        <ul class="mb-0">
                                            @foreach($overviewEvent->options as $opt)
                                                <li>{{ $opt->option_text }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <h6>5) QR Code</h6>
                            <div class="small text-muted">Your QR has been generated in Step 6.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        @if(!empty($booking))
                        <form method="POST" action="{{ route('voting.complete') }}">
                            @csrf
                            <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                            <button type="submit" class="btn btn-success">Confirm Finish</button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

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
 
            if (currentStep === 2) {
                const cards = document.querySelectorAll('.selectable-card');
                const nextBtn = document.getElementById('wizardNextBtn');
                const selectedInfo = document.querySelector('.selected-info');
                const selectedInfoTariff = document.querySelector('.selected-info-tariff');
                const selectedInput = document.getElementById('selectedTariffInput');
                const termsCheckbox = document.getElementById('termsCheckbox');
                let selectedTariffId = @json($selectedTariff ? $selectedTariff->id : '');

                // Enable Next only when tariff + checkbox are valid
                function updateNextButtonState() {
                    if (selectedTariffId && (!termsCheckbox || termsCheckbox.checked)) {
                        nextBtn.disabled = false;
                    } else {
                        nextBtn.disabled = true;
                    }
                }

                function setSelected(cardEl) {
                    cards.forEach(c => c.classList.remove('selected'));
                    cardEl.classList.add('selected');
                    selectedTariffId = cardEl.getAttribute('data-tariff-id');
                    const header = cardEl.querySelector('.card-header strong');
                    const title = header ? header.innerText : 'Tariff';

                    let isPaid = @json($booking && $booking->payment_status === 'succeeded');

                    // Inject checkbox into DOM
                    selectedInfo.innerHTML = `
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="termsCheckbox" ${isPaid ? 'checked' : ''}>
                            <label class="form-check-label" for="termsCheckbox">
                                I agree to the <a href="{{ route('terms.show') }}" target="_blank">Terms & Conditions</a>
                            </label>
                        </div>
                    `;
                    selectedInfoTariff.innerHTML = `
                    <strong>Selected: </strong> ${title} 
                    `;
                    selectedInput.value = selectedTariffId;

                    // Get new checkbox
                    const newCheckbox = document.getElementById('termsCheckbox');

                    if (isPaid) {
                        nextBtn.disabled = false;
                        if (newCheckbox) {
                            newCheckbox.disabled = true; 
                        }
                    } else {
                        nextBtn.disabled = true;
                        if (newCheckbox) {
                            newCheckbox.addEventListener('change', function () {
                                nextBtn.disabled = !this.checked;
                            });
                        }
                    }
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

                // Attach listener if checkbox already in DOM
                if (termsCheckbox) {
                    termsCheckbox.addEventListener('change', updateNextButtonState);
                }

                // Initialize state if already selected
                if (selectedTariffId) {
                    const initialCard = [...cards].find(c => c.getAttribute('data-tariff-id') === selectedTariffId.toString());
                    if (initialCard) {
                        setSelected(initialCard);
                    }
                } else {
                    updateNextButtonState();
                }
            }
        });
    </script>
    @endpush
</x-app-layout>