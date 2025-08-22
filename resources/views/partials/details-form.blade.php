{{-- resources/views/partials/details-form.blade.php --}}

<form class="details-form reward-form" method="POST" action="{{ route('voting.create.step', ['step' => $currentStep]) }}" enctype="multipart/form-data" novalidate>
    @csrf

    <div class="mb-3">
        <label for="form_name" class="form-label">Name of voting form</label>
        <input type="text" class="form-control" id="form_name" name="form_name" placeholder="e.g. Best Player of the Match" required value="{{ old('form_name') }}">
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <label for="start_at" class="form-label">Voting start (date & time)</label>
            <input type="datetime-local" class="form-control" id="start_at" name="start_at" required value="{{ old('start_at') }}">
        </div>
        <div class="col-md-6">
            <label for="end_at" class="form-label">Voting end (date & time)</label>
            <input type="datetime-local" class="form-control" id="end_at" name="end_at" required value="{{ old('end_at') }}">
        </div>
    </div>

    <div class="mb-3 mt-2">
        <label for="question" class="form-label">Voting question</label>
        <textarea class="form-control" id="question" name="question" rows="2" placeholder="Write the question participants will see" required>{{ old('question') }}</textarea>
    </div>

   <div class="mb-3">
    <label class="form-label">Voting options</label>
    <div class="options-list" id="optionsList">
        <div class="option-item mb-2">
            <input type="text" class="form-control" id="option1" name="options[]" placeholder="Option 1" required value="{{ old('options.0') ?? '' }}">
        </div>
        <div class="option-item mb-2">
            <input type="text" class="form-control" id="option2" name="options[]" placeholder="Option 2" required value="{{ old('options.1') ?? '' }}">
        </div>
        <div class="option-item mb-2">
            <input type="text" class="form-control" id="option3" name="options[]" placeholder="Option 3" value="{{ old('options.2') ?? '' }}">
        </div>
        <div class="option-item mb-2">
            <input type="text" class="form-control" id="option4" name="options[]" placeholder="Option 4" value="{{ old('options.3') ?? '' }}">
        </div>
    </div>

    <div class="text-end">
    <button type="button" class="btn btn-success btn-sm mt-2" id="addOptionBtn">
        + Add Option
    </button>
    </div>

</div>

    <div class="d-flex justify-content-between mt-4">
        @php
            $prev = $currentStep - 1;
            $prevUrl = $prev >= 1 ? route('voting.create.step', ['step' => $prev] + (request()->query() ? request()->query() : [])) : route('voting.realized');
        @endphp

        <a href="{{ $prevUrl }}" class="btn btn-light">{{ $prev >= 1 ? 'Back' : 'Cancel' }}</a>

        <button type="submit" id="detailsNextBtn" class="btn btn-success">Save & Next</button>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('.details-form');

 
    if (form) {
        form.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA')) {
                e.preventDefault();
            }
        });
    }

    form.addEventListener('submit', function (e) {
        
        const name = document.getElementById('form_name').value.trim();
        const start = document.getElementById('start_at').value;
        const end = document.getElementById('end_at').value;
        const question = document.getElementById('question').value.trim();

        const optionInputs = Array.from(document.querySelectorAll('#optionsList input[name="options[]"]'));
        const optionsValues = optionInputs.map(i => i.value.trim());

        ['form_name','start_at','end_at','question'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.classList.remove('is-invalid');
        });
        optionInputs.forEach(i => i.classList.remove('is-invalid'));

        const errors = [];
        if (!name) { errors.push('Please enter the form name'); document.getElementById('form_name').classList.add('is-invalid'); }
        if (!start) { errors.push('Please select voting start date & time'); document.getElementById('start_at').classList.add('is-invalid'); }
        if (!end) { errors.push('Please select voting end date & time'); document.getElementById('end_at').classList.add('is-invalid'); }
        if (start && end && (new Date(start) >= new Date(end))) { errors.push('End time must be after start time'); document.getElementById('end_at').classList.add('is-invalid'); }
        if (!question) { errors.push('Please enter the voting question'); document.getElementById('question').classList.add('is-invalid'); }

        const filledOptions = optionsValues.filter(v => v.length > 0);
        if (filledOptions.length < 2) {
            errors.push('Please provide at least two answer options (Option 1 and 2 at minimum)');
            
            if (optionInputs[0]) optionInputs[0].classList.add('is-invalid');
            if (optionInputs[1]) optionInputs[1].classList.add('is-invalid');
        }

        if (errors.length) {
            // prevent submit and show first error
            e.preventDefault();
            alert(errors[0]);
            return;
        }

    });

    let optionCount = document.querySelectorAll('#optionsList input[name="options[]"]').length || 4;

    document.getElementById("addOptionBtn").addEventListener("click", function () {
        optionCount++;
        const optionsList = document.getElementById("optionsList");

        const newOption = document.createElement("div");
        newOption.classList.add("option-item", "mb-2");

        newOption.innerHTML = `
            <input type="text" class="form-control" 
                   id="option${optionCount}" 
                   name="options[]" 
                   placeholder="Option ${optionCount}">
        `;

        optionsList.appendChild(newOption);
    });
});
</script>
@endpush
