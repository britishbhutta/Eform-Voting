{{-- resources/views/partials/reward-form.blade.php --}}

<form class="reward-form" method="POST" action="{{ route('voting.create.step', ['step' => $currentStep]) }}" enctype="multipart/form-data" novalidate>
    @csrf  {{-- CSRF protection retained for when you enable real submit later --}}

    {{-- Intro --}}
    <p class="form-intro">Insert an interesting gift - a voucher for voters.</p>

    <div class="mb-3">
        <label for="reward_name" class="form-label">Name of reward</label>
        <input type="text" class="form-control" id="reward_name" name="reward_name" required>
    </div>

    <div class="mb-3">
        <label for="reward_description" class="form-label">Describe reward</label>
        <textarea class="form-control" id="reward_description" name="reward_description" rows="4" required></textarea>
    </div>

    <div class="mb-3">
        <label for="reward_image" class="form-label">Upload reward (image)</label>
        <input type="file" class="form-control" id="reward_image" name="reward_image" accept="image/*" required>
    </div>

    {{-- Wizard actions area --}}
    <div class="d-flex justify-content-between mt-4">
        @php
            $prev = $currentStep - 1;
            $prevUrl = $prev >= 1 ? route('voting.create.step', ['step' => $prev] + (request()->query() ? request()->query() : [])) : route('voting.realized');

            // Build client-side next URL (preserve query string if present)
            $nextStep = $currentStep + 1;
            $nextBase = route('voting.create.step', ['step' => $nextStep]);
            $qs = request()->getQueryString();
            $nextUrl = $nextBase . ($qs ? ('?' . $qs) : '');
        @endphp

        <a href="{{ $prevUrl }}" class="btn btn-light">{{ $prev >= 1 ? 'Back' : 'Cancel' }}</a>

        {{-- Changed type to button so it doesn't submit --}}
        <button type="button" id="rewardNextBtn" class="btn btn-success">Next</button>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Prevent actual form submission by Enter or other means
    const form = document.querySelector('.reward-form');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
        });

        // Prevent Enter key submitting the form
        form.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA')) {
                // If you ever want to allow Enter in textarea, check e.target.tagName
                e.preventDefault();
            }
        });
    }

    // Next button: navigate client-side to next wizard step (preserves query string)
    const nextBtn = document.getElementById('rewardNextBtn');
    if (nextBtn) {
        // nextUrl is injected server-side for correctness
        const nextUrl = @json($nextUrl);
        nextBtn.addEventListener('click', function () {
            // If you want to do client-side validation before navigating, do it here.
            // Example (simple): require reward_name to be non-empty
            // const name = document.getElementById('reward_name').value.trim();
            // if (!name) { alert('Please enter reward name'); return; }

            window.location.href = nextUrl;
        });
    }
});
</script>
@endpush
