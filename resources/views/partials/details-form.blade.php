{{-- resources/views/partials/details-form.blade.php --}}
@php
    $currentStep = $currentStep ?? 5;

    $prev = $currentStep - 1;
    $prevUrl = $prev >= 1
        ? route('voting.create.step', ['step' => $prev] + (request()->query() ? request()->query() : []))
        : route('voting.realized');

    $bookingId = $booking->id ?? session('voting.booking_id') ?? old('booking_id') ?? request()->input('booking_id');

    $votingData = $votingData ?? [
        'form_name' => '',
        'question' => '',
        'start_at' => '',
        'end_at' => '',
        'options' => [],
    ];

    $initialOptions = old('options') ?? ($votingData['options'] ?? []);
    $initialCount = max(4, count($initialOptions));
@endphp

<form class="details-form reward-form" method="POST" action="{{ route('voting.create.step', ['step' => $currentStep]) }}" enctype="multipart/form-data" novalidate>
    @csrf

    <!-- @if($bookingId)
        <input type="hidden" name="booking_id" value="{{ $bookingId }}">
    @endif -->

    <div class="mb-3">
        <label for="form_name" class="form-label">Name of Voting Form*</label>
        <input type="text" class="form-control @error('form_name') is-invalid @enderror" id="form_name" name="form_name" placeholder="e.g. Best Player of the Match" required value="{{ old('form_name', $votingData['form_name'] ?? '') }}">
        @error('form_name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <label for="start_at" class="form-label">
                Voting Start (Date & Time)* 
                @if($localTime)
                    <small class="text-muted"> {{ $localTime }}</small>
                @endif
            </label>
            <input type="datetime-local" class="form-control @error('start_at') is-invalid @enderror" id="start_at" name="start_at" required value="{{ old('start_at', $votingData['start_at'] ?? '') }}">
            @error('start_at') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label for="end_at" class="form-label">
                Voting End (Date & Time)*
                @if($localTime)
                    <small class="text-muted"> {{ $localTime }}</small>
                @endif
            </label>
            <input type="datetime-local" class="form-control @error('end_at') is-invalid @enderror" id="end_at" name="end_at" required value="{{ old('end_at', $votingData['end_at'] ?? '') }}">
            @error('end_at') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="mb-3 mt-2">
        <label for="question" class="form-label">Voting Question*</label>
        <textarea class="form-control @error('question') is-invalid @enderror" id="question" name="question" rows="2" placeholder="Write the question participants will see" required>{{ old('question', $votingData['question'] ?? '') }}</textarea>
        @error('question') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">Voting Options*</label>
        <div class="options-list" id="optionsList">
            @for($i = 0; $i < $initialCount; $i++)
                @php $val = $initialOptions[$i] ?? ''; @endphp
                <div class="option-item mb-2">
                    <input type="text" class="form-control @error('options.'.$i) is-invalid @enderror" id="option{{ $i+1 }}" name="options[]" placeholder="Option {{ $i+1 }}" value="{{ $val }}">
                    @error('options.'.$i) <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            @endfor
        </div>

        <div class="text-end">
            <button type="button" class="btn btn-success btn-sm mt-2" id="addOptionBtn">
                + Add Option
            </button>
        </div>
    </div>

    <div class="d-flex justify-content-between mt-4">
        <a href="{{ $prevUrl }}" class="btn btn-light">{{ $prev >= 1 ? 'Back' : 'Cancel' }}</a>

        <button type="submit" id="detailsNextBtn" class="btn btn-success">Save</button>
    </div>
</form>
<!-- Confirmation Modal -->
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
                    <div>{{ $selectedTariff?->title ?? '-' }} — {{ $selectedTariff ? number_format($selectedTariff->price_cents/100,2) . ' ' . $selectedTariff->currency : '' }}</div>
                </div>
                <div class="mb-3">
                    <h6>2) Billing / Booking</h6>
                    @if(!empty($booking))
                        <div class="small">
                            <div><strong>Name:</strong> {{ $booking->name ?? '-' }}</div>
                            <div><strong>Email:</strong> {{ $booking->email ?? '-' }}</div>
                            <div><strong>Address:</strong> {{ $booking->address ?? '-' }}, {{ $booking->city ?? '-' }} {{ $booking->zip ?? '' }}</div>
                            <div><strong>Country:</strong> {{ $booking->country ?? '-' }}</div>
                            <div><strong>Reference:</strong> {{ $booking->booking_reference ?? '-' }}</div>
                        </div>
                    @else
                        <div class="text-danger">No booking found.</div>
                    @endif
                </div>
                <div class="mb-3">
                    <h6>3) Reward</h6>
                    @php $reward = $booking?->reward; @endphp
                    <div class="small">
                        <div><strong>Name:</strong> {{ $reward->name ?? '-' }}</div>
                        <div><strong>Description:</strong> {{ $reward->description ?? '-' }}</div>
                    </div>
                </div>
                <div class="mb-3">
                    <h6>4) Voting Event</h6>
                    @php $overviewEvent = isset($votingEvent) ? $votingEvent : (isset($booking) ? \App\Models\VotingEvent::with('options')->where('booking_id', $booking->id)->first() : null); @endphp
                    <div class="small" id="modal-form-detail">
                        <div><strong>Title:</strong> {{ $overviewEvent?->title ?? '-' }}</div>
                        <div><strong>Question:</strong> {{ $overviewEvent?->question ?? '-' }}</div>


                        
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
                    <div class="small">Your QR Code would generate after the Payment.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                @if(!empty($booking))
                    <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                    <a href="{{ route('voting.create.step', ['step' => 5]) }}" type="submit" class="btn btn-success">Confirm & Next</a>
                @endif
            </div>
        </div>
    </div>
</div>
  

@push('scripts')
    <script src="{{asset('/')}}js/step4-ajax.js"></script>
@endpush
