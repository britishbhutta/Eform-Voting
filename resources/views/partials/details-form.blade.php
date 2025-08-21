
{{-- resources/views/partials/details-form.blade.php --}}

<form class="details-form reward-form" method="POST" action="{{ route('voting.create.step', ['step' => $currentStep]) }}" enctype="multipart/form-data" novalidate>
    @csrf

    

    <div class="mb-3">
        <label for="form_name" class="form-label">Name of voting form</label>
        <input type="text" class="form-control" id="form_name" name="form_name" placeholder="e.g. Best Player of the Match" required>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <label for="start_at" class="form-label">Voting start (date & time)</label>
            <input type="datetime-local" class="form-control" id="start_at" name="start_at" required>
        </div>
        <div class="col-md-6">
            <label for="end_at" class="form-label">Voting end (date & time)</label>
            <input type="datetime-local" class="form-control" id="end_at" name="end_at" required>
        </div>
    </div>

    <div class="mb-3 mt-2">
        <label for="question" class="form-label">Voting question</label>
        <textarea class="form-control" id="question" name="question" rows="2" placeholder="Write the question participants will see" required></textarea>
    </div>

    <div class="mb-3">
        <label class="form-label">Voting options</label>
        <div class="options-list">
            <div class="option-item mb-2">
                <input type="text" class="form-control" id="option1" name="option1" placeholder="Option 1" required>
            </div>
            <div class="option-item mb-2">
                <input type="text" class="form-control" id="option2" name="option2" placeholder="Option 2" required>
            </div>
            <div class="option-item mb-2">
                <input type="text" class="form-control" id="option3" name="option3" placeholder="Option 3">
            </div>
            <div class="option-item mb-2">
                <input type="text" class="form-control" id="option4" name="option4" placeholder="Option 4 ">
            </div>
        </div>
    </div>

    {{-- Wizard actions area --}}
    <div class="d-flex justify-content-between mt-4">
        @php
            $prev = $currentStep - 1;
            $prevUrl = $prev >= 1 ? route('voting.create.step', ['step' => $prev] + (request()->query() ? request()->query() : [])) : route('voting.realized');

            // Next (client-side navigation)
            $nextStep = $currentStep + 1;
            $nextBase = route('voting.create.step', ['step' => $nextStep]);
            $qs = request()->getQueryString();
            $nextUrl = $nextBase . ($qs ? ('?' . $qs) : '');
        @endphp

        <a href="{{ $prevUrl }}" class="btn btn-light">{{ $prev >= 1 ? 'Back' : 'Cancel' }}</a>

        <button type="button" id="detailsNextBtn" class="btn btn-success">Next</button>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Prevent actual form submission
    const form = document.querySelector('.details-form');
    if (form) {
        form.addEventListener('submit', function (e) { e.preventDefault(); });
        form.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA')) {
                e.preventDefault();
            }
        });
    }

    const nextBtn = document.getElementById('detailsNextBtn');
    if (!nextBtn) return;

    nextBtn.addEventListener('click', function () {
        // Simple client-side validation before moving to next step
        const name = document.getElementById('form_name').value.trim();
        const start = document.getElementById('start_at').value;
        const end = document.getElementById('end_at').value;
        const question = document.getElementById('question').value.trim();
        const opts = [
            document.getElementById('option1').value.trim(),
            document.getElementById('option2').value.trim(),
            document.getElementById('option3').value.trim(),
            document.getElementById('option4').value.trim(),
        ];

        // reset invalid classes
        [ 'form_name','start_at','end_at','question','option1','option2' ].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.classList.remove('is-invalid');
        });

        const errors = [];
        if (!name) { errors.push('Please enter the form name'); document.getElementById('form_name').classList.add('is-invalid'); }
        if (!start) { errors.push('Please select voting start date & time'); document.getElementById('start_at').classList.add('is-invalid'); }
        if (!end) { errors.push('Please select voting end date & time'); document.getElementById('end_at').classList.add('is-invalid'); }
        if (start && end && (new Date(start) >= new Date(end))) { errors.push('End time must be after start time'); document.getElementById('end_at').classList.add('is-invalid'); }
        if (!question) { errors.push('Please enter the voting question'); document.getElementById('question').classList.add('is-invalid'); }

        // require at least two non-empty options
        const filledOptions = opts.filter(v => v.length > 0);
        if (filledOptions.length < 2) { errors.push('Please provide at least two answer options (Option 1 and 2 at minimum)');
            document.getElementById('option1').classList.add('is-invalid');
            document.getElementById('option2').classList.add('is-invalid');
        }

        if (errors.length) {
            // Show first error as alert (you can replace with nicer UI later)
            alert(errors[0]);
            return;
        }

        // All good — navigate to next step (preserves query string because server rendered nextUrl)
        const nextUrl = @json($nextUrl);
        window.location.href = nextUrl;
    });
});
</script>
@endpush
