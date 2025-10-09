// Validation
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
            e.preventDefault();
            validate = true;
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
                e.preventDefault();
                alert(errors[0]); 
                validate = false;
                return;
            }
             if(validate == true){
                const formData = new FormData(form);
                formData.append('_token', $('meta[name="csrf-token"]').attr('content')); // add CSRF
                $.ajax({
                    url: form.action,   
                    method: form.method, 
                    data: formData,     
                    processData: false,  
                    contentType: false,  
                    success: function(response) {
                        if(response.status == 'success'){   
                            // alert(response.votingEvent['title'])
                            let event = response.votingEvent;
                            let tz    = response.countryTimezone;
                            // Build options list
                            let optionsHtml = '-';
                            if (event.options && event.options.length > 0) {
                                optionsHtml = '<ul class="mb-0">';
                                event.options.forEach(opt => {
                                    optionsHtml += `<li>${opt.option_text}</li>`;
                                });
                                optionsHtml += '</ul>';
                            }
                            let html = `
                                <div><strong>Title:</strong> ${event.title ?? '-'}</div>
                                <div><strong>Question:</strong> ${event.question ?? '-'}</div>
                                <div><strong>Options:</strong> ${optionsHtml}</div>
                                <div><strong>Start:</strong> ${response.start_at} ${response.localTime}</div>
                                <div><strong>End:</strong> ${response.end_at} ${response.localTime}</div>
                            `;
                            $('#modal-form-detail').html(html);
                            $('#finishOverviewModal').modal('show');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr.responseText);
                        alert('Something went wrong.');
                    }
                });
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


    
//