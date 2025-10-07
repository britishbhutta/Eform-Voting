@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <style>
        
        /* hide unwanted fields from Stripe Address Element */
            .StripeElement--address input[name="name"],
            .StripeElement--address input[name="line1"],
            .StripeElement--address input[name="line2"],
            .StripeElement--address input[name="city"],
            .StripeElement--address input[name="postal_code"],
            .StripeElement--address select[name="country"] {
                display: none !important;
            }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@25.10.1/build/css/intlTelInput.css">
    @if ($errors->any())
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                @foreach ($errors->all() as $error)
                
                    Toastify({
                        text: "{{ $error }}",
                        duration: 3000,
                        gravity: "top", 
                        position: "right", 
                        backgroundColor: "linear-gradient(to right, #ff5f6d, #ffc371)",
                        stopOnFocus: true,
                        close: true
                    }).showToast();
                @endforeach
            });
        </script>
    @endif
            
@endpush

<div class="container d-flex justify-content-center">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm p-4">
                <h5 class="text-center mb-5">Personal Information</h5>
                <form action="{{ route('voting.create.step',['step' => 1]) }}" id="personal-info-form" method="POST">
                    @csrf
                    <!-- Email -->
                    <div class="row align-items-center mb-2">
                        <label class="col-sm-3 col-form-label">Email*</label>
                        <div class="col-sm-9">
                        <input type="email" name="email" class="form-control" placeholder="Enter your email" value="{{ old('email', $personalInfoData['email'] ?? '') }}" required>
                        <div class="invalid-feedback">Please enter a valid email.</div>
                        </div>
                    </div>

                    <!-- Phone -->
                    <div class="row align-items-center mb-2">
                        <label class="col-sm-3 col-form-label">Phone Number</label>
                        <div class="col-sm-9">
                            <input type="tel" id="phone" placeholder="" id="telephone" name="phone" value="{{ old('phone', $personalInfoData['phone'] ?? '') }}" class="form-control" inputmode="numeric" maxlength="15"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                placeholder="Phone" style="width: 400px;" required>
                                <div class="invalid-feedback">Please enter Phone Number.</div>
                        </div>
                    </div>

                    <!-- Invoice Checkbox (full width) -->
                    <div class="row mb-2">
                        <div class="col-sm-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="invoice_issued" id="invoiceCompany" {{ session('issued_invoice') ? 'checked' : '' }}>
                                <label class="form-check-label" for="invoiceCompany">
                                Invoice issued to a company
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Company -->
                    <div class="row align-items-center mb-2">
                        <label class="col-sm-3 col-form-label">Company*</label>
                        <div class="col-sm-9">
                            <input type="text" name="company" class="form-control" placeholder="Enter company name" value="{{ old('company', $personalInfoData['company'] ?? '') }}" required>
                            <div class="invalid-feedback">Please enter Company Name.</div>
                        </div>
                    </div>

                    <!-- Company ID -->
                    <div class="row align-items-center mb-2">
                        <label class="col-sm-3 col-form-label">Company ID*</label>
                        <div class="col-sm-9">
                        <input type="text" name="company_id" class="form-control" placeholder="Enter company ID" value="{{ old('company_id', $personalInfoData['company_id'] ?? '') }}" required>
                        <div class="invalid-feedback">Please enter Company ID.</div>
                        </div>
                    </div>

                    <!-- Tax/VAT Number -->
                    <div class="row align-items-center mb-2">
                        <label class="col-sm-3 col-form-label">Tax/VAT Number</label>
                        <div class="col-sm-9">
                        <input type="text" name="tax_vat_no" class="form-control" placeholder="Enter tax/VAT number" value="{{ old('tax_vat_no', $personalInfoData['tax_vat_no'] ?? '') }}">
                        <div class="invalid-feedback">Please enter Tax/Vat Number.</div>
                        </div>
                    </div>

                    <!-- First Name -->
                    <div class="row align-items-center mb-2">
                        <label class="col-sm-3 col-form-label">First Name *</label>
                        <div class="col-sm-9">
                        <input type="text" name="fname" class="form-control" placeholder="Enter first name" value="{{ old('fname', $personalInfoData['fname'] ?? '') }}" required>
                        <div class="invalid-feedback">Please enter First Name.</div>
                        </div>
                    </div>

                    <!-- Last Name -->
                    <div class="row align-items-center mb-2">
                        <label class="col-sm-3 col-form-label">Last Name *</label>
                        <div class="col-sm-9">
                        <input type="text" name="lname" class="form-control" placeholder="Enter last name" value="{{ old('lname', $personalInfoData['lname'] ?? '') }}" required>
                        <div class="invalid-feedback">Please enter Last Name.</div>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="row align-items-center mb-2">
                        <label class="col-sm-3 col-form-label">Address*</label>
                        <div class="col-sm-9">
                        <input type="text" name="address" class="form-control" placeholder="Enter address" value="{{ old('address', $personalInfoData['address'] ?? '') }}" required>
                        <div class="invalid-feedback">Please Enter Address.</div>
                        </div>
                    </div>

                    <!-- City -->
                    <div class="row align-items-center mb-2">
                        <label class="col-sm-3 col-form-label">City*</label>
                        <div class="col-sm-9">
                        <input type="text" name="city" class="form-control" placeholder="Enter city" value="{{ old('city', $personalInfoData['city'] ?? '') }}" required>
                        <div class="invalid-feedback">Please Enter City.</div>
                        </div>
                    </div>

                    <!-- ZIP -->
                    <div class="row align-items-center mb-2">
                        <label class="col-sm-3 col-form-label">ZIP*</label>
                        <div class="col-sm-9">
                        <input type="text" name="zip" class="form-control" placeholder="Enter ZIP code" value="{{ old('zip', $personalInfoData['zip'] ?? '') }}" required>
                        <div class="invalid-feedback">Please enter a valid Zip Code.</div>
                        </div>
                    </div>

                    <!-- Country -->
                    <div class="row align-items-center mb-2">
                        <label class="col-sm-3 col-form-label">Country*</label>
                        <div class="col-sm-9">
                            <select name="country" id="country-select" class="form-control" required>
                                <option value="">{{ $countries->isEmpty() ? 'No countries available' : 'Select Country' }}</option>

                                {{-- Pre-filled country from $personalInfoData if available --}}
                                @if(!empty($personalInfoData['country']))
                                    <option value="{{ $personalInfoData['country'] }}" 
                                        {{ old('country_id', $personalInfoData['country'] ?? '') == $personalInfoData['country'] ? 'selected' : '' }}>
                                        {{ $personalInfoData['country_name'] ?? '' }}
                                    </option>
                                @endif

                                {{-- Countries list --}}
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}" 
                                        {{ old('country_id', $personalInfoData['country'] ?? '') == $country->id ? 'selected' : '' }}>
                                        {{ $country->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please Select Country.</div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 

<div class="d-flex justify-content-between mt-4">
    @php
        $nextStep = ($currentStep ?? 1) + 1;
        $nextBase = route('voting.create.step', ['step' => $nextStep]);
        $qs = request()->getQueryString();
        $nextUrl = $nextBase . ($qs ? ('?' . $qs) : '');
    @endphp
    
    <a href="{{ route('voting.realized') }}" class="btn btn-light me-2">Cancel</a>

    {{-- Changed type to button so it doesn't submit --}}
    <button type="button" id="selectTariffNextBtn" class="btn btn-success">Next</button>
</div>
@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@25.10.1/build/js/intlTelInput.min.js"></script>
    <script type="text/javascript">
        const input = document.querySelector("#phone");

        // This comes from Laravel: e.g. "IN", "US", "DE"
        let initialCountryCode = @json(old('countryCode', $personalInfoData['countryISOCode'] ?? ''));
        

        const iti = window.intlTelInput(input, {
            initialCountry: initialCountryCode ? initialCountryCode.toLowerCase() : "auto",
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@25.10.1/build/js/utils.js"
        });

        // Only fallback to ipapi if we don’t already have a country
        if (!initialCountryCode) {
            fetch("https://ipapi.co/country/")
                .then(res => res.text())
                .then(countryCode => {
                    iti.setCountry(countryCode.toLowerCase());
                });
        }
    </script>

    <script>
        document.getElementById("selectTariffNextBtn").addEventListener("click", function () {
            let form = document.getElementById("personal-info-form");
            let inputs = form.querySelectorAll("input, select, textarea");

            let valid = true;

            inputs.forEach(function(input) {
                // Reset previous states
                input.classList.remove("is-valid", "is-invalid");

                // If field is required OR has a value, validate it
                if (input.required || input.value.trim() !== "") {
                    if (!input.checkValidity()) {
                        input.classList.add("is-invalid");
                        valid = false;
                    } else {
                        input.classList.add("is-valid");
                    }
                }
            });

            if (valid) {
                let rawNumber = input.value;
                if (rawNumber.startsWith("0")) {
                    rawNumber = rawNumber.substring(1);
                }
                let countryCode = iti.getSelectedCountryData().dialCode;
               //$('#phone').val(countryCode + rawNumber);
                $('#phone').val(countryCode + '-' + rawNumber);
                form.submit(); // safe now
            }

        });
    </script>
@endpush
