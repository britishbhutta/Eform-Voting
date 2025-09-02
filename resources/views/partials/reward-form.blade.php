{{-- resources/views/partials/reward-form.blade.php --}}

@php

    $currentStep = $currentStep ?? 3;

    $prev = $currentStep - 1;
    $prevUrl = $prev >= 1
        ? route('voting.create.step', ['step' => $prev] + (request()->query() ? request()->query() : []))
        : route('voting.realized');

    $backLabel = $prev >= 1 ? 'Back' : 'Cancel';

    $bookingId = $booking->id ?? session('voting.booking_id') ?? old('booking_id') ?? request()->input('booking_id');


    $rewardData = $rewardData ?? [];
@endphp

<form class="reward-form" method="POST"
      action="{{ route('voting.create.step', ['step' => $currentStep]) }}"
      enctype="multipart/form-data" novalidate>
    @csrf

   
    @if($bookingId)
        <input type="hidden" name="booking_id" value="{{ $bookingId }}">
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="mb-3">
        <label for="reward_name" class="form-label">Name of Reward*</label>
        <input type="text"
               class="form-control @error('reward_name') is-invalid @enderror"
               id="reward_name"
               name="reward_name"
               required
               value="{{ old('reward_name', $rewardData['reward_name'] ?? '') }}">
        @error('reward_name')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>


    <div class="mb-3">
        <label for="reward_description" class="form-label">Describe Reward</label>
        <textarea class="form-control @error('reward_description') is-invalid @enderror"
                  id="reward_description"
                  name="reward_description"
                  rows="4">{{ old('reward_description', $rewardData['reward_description'] ?? '') }}</textarea>
        @error('reward_description')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

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

      @php
    use Illuminate\Support\Facades\Storage;

    $raw = old('existing_reward_image', $rewardData['reward_image'] ?? null);

    $imgPath = null;
    if ($raw) {
        $imgPath = preg_replace('#^(/?storage/)|^(/)#', '', trim($raw));
    }

    $imgExists = $imgPath ? Storage::disk('public')->exists($imgPath) : false;

    $publicUrl = $imgExists ? asset('storage/' . $imgPath) : null;
    $filename = $imgPath ? basename($imgPath) : null;
        @endphp

        @if($imgPath && $imgExists)
            <div class="mt-2">
                <p class="small mb-1">Current image:</p>
                <img src="{{ $publicUrl }}" alt="reward image preview" style="max-height:150px;">
                {{-- <p class="small">Filename: <strong>{{ $filename }}</strong></p> --}}
                {{-- <p class="small"  ><a href="{{ $publicUrl }}" target="_blank"  rel="noopener">Open image in new tab</a></p> --}}
                <input type="hidden" name="existing_reward_image" value="{{ $imgPath }}">
            </div>
        @elseif($imgPath)
            <div class="mt-2">
                <div class="alert alert-warning p-2">
                    Image path exists in DB/session but file was <strong>not found</strong> on disk.
                </div>
                <p class="small text-muted">Stored path: <code>{{ $raw }}</code></p>
                <p class="small text-muted">Expected location: <code>storage/app/public/{{ $imgPath }}</code></p>
                @if($filename)
                    <p class="small text-muted">Filename: <code>{{ $filename }}</code></p>
                @endif
            </div>
        @endif

    </div>
    <div class="d-flex justify-content-between mt-4">
        <a href="{{ $prevUrl }}" class="btn btn-light">{{ $backLabel }}</a>

        <button type="submit" class="btn btn-success">Next</button>
    </div>
</form>
