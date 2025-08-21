{{-- resources/views/partials/reward-form.blade.php --}}

@php
    // Ensure $currentStep is available (fallback to 3)
    $currentStep = $currentStep ?? 3;

    $prev = $currentStep - 1;
    $prevUrl = $prev >= 1
        ? route('voting.create.step', ['step' => $prev] + (request()->query() ? request()->query() : []))
        : route('voting.realized');

    $backLabel = $prev >= 1 ? 'Back' : 'Cancel';

    $bookingId = session('voting.booking_id') ?? old('booking_id') ?? request()->input('booking_id');
@endphp

@if($bookingId)
    <input type="hidden" name="booking_id" value="{{ $bookingId }}">
@endif

<form class="reward-form" method="POST"
      action="{{ route('voting.create.step', ['step' => $currentStep]) }}"
      enctype="multipart/form-data" novalidate>
    @csrf

    {{-- Server flash / status --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Name --}}
    <div class="mb-3">
        <label for="reward_name" class="form-label">Name of reward</label>
        <input type="text"
               class="form-control @error('reward_name') is-invalid @enderror"
               id="reward_name"
               name="reward_name"
               required
               value="{{ old('reward_name') }}">
        @error('reward_name')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    {{-- Description --}}
    <div class="mb-3">
        <label for="reward_description" class="form-label">Describe reward</label>
        <textarea class="form-control @error('reward_description') is-invalid @enderror"
                  id="reward_description"
                  name="reward_description"
                  rows="4">{{ old('reward_description') }}</textarea>
        @error('reward_description')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    {{-- Image --}}
    <div class="mb-3">
        <label for="reward_image" class="form-label">Upload reward (image)</label>
        <input type="file"
               class="form-control @error('reward_image') is-invalid @enderror"
               id="reward_image"
               name="reward_image"
               accept="image/*">
        @error('reward_image')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    {{-- Hidden booking id so controller receives it (if available) --}}
    @if($bookingId)
        <input type="hidden" name="booking_id" value="{{ $bookingId }}">
    @endif

    {{-- actions --}}
    <div class="d-flex justify-content-between mt-4">
        <a href="{{ $prevUrl }}" class="btn btn-light">{{ $backLabel }}</a>

        {{-- Submit the form to save reward and go to next step --}}
        <button type="submit" class="btn btn-success">Next</button>
    </div>
</form>
