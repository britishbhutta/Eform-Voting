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
@endpush

 @if($selectedTariff)
        <div class="mb-3">
            <div class="alert alert-light">
                <strong>Selected tariff:</strong>
                {{ $selectedTariff->title }}
                — {{ number_format($selectedTariff->price_cents / 100, 2) }} {{ $selectedTariff->currency }}
            </div>
        </div>
    @endif

 
    <div class="captcha-container" id="captcha-container">
        <div class="captcha-success" id="captcha-success">
            <i class="bi bi-check-circle"></i>
            <h4>Verification Successful!</h4>
            <p>You have been verified as human. Please proceed with your payment.</p>
        </div>
        
        <div id="captcha-widget">
            <div class="form-label">Please verify you're human</div>
            <div class="cf-turnstile"
                 data-sitekey="{{ config('services.turnstile.site_key') }}"
                 data-theme="dark"
                 data-callback="onCaptchaSuccess">
            </div>
        </div>
    </div>

    <form action="{{ route('stripe.payment') }}" id="stripe-form" method="POST">
        <div class="payment-form-container" id="payment-form-wrapper">
            <div class="container mt-3">
                <div id="payment-form-container">
                    
                        @csrf
                        <input type="hidden" name="stripeToken" id="stripe-token">
                        <input type="hidden" name="cf-turnstile-response" id="cf-turnstile-response">
                        <input type="hidden" name="selectedTariffId" value="{{ $selectedTariff->id }}">
                        <div class="row">
                        <div class="col-md-6">
                            <div class="card shadow-sm p-4">
                            <h5 class="text-center mb-5">Billing information</h5>

                            <!-- Email -->
                            <div class="row align-items-center mb-2">
                                <label class="col-sm-3 col-form-label">Email*</label>
                                <div class="col-sm-9">
                                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="row align-items-center mb-2">
                                <label class="col-sm-3 col-form-label">Phone Number</label>
                                <div class="col-sm-9">
                                    <input type="tel" id="phone" placeholder="" id="telephone" name="phone_number" class="form-control" inputmode="numeric" maxlength="15"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                        placeholder="Phone" style="width: 400px;">
                                </div>
                            </div>

                            <!-- Invoice Checkbox (full width) -->
                            <div class="row mb-2">
                                <div class="col-sm-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="invoice_issued" id="invoiceCompany">
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
                                    <input type="text" name="company_name" class="form-control" placeholder="Enter company name" required>
                                </div>
                            </div>

                            <!-- Company ID -->
                            <div class="row align-items-center mb-2">
                                <label class="col-sm-3 col-form-label">Company ID*</label>
                                <div class="col-sm-9">
                                <input type="text" name="company_id" class="form-control" placeholder="Enter company ID" required>
                                </div>
                            </div>

                            <!-- Tax/VAT Number -->
                            <div class="row align-items-center mb-2">
                                <label class="col-sm-3 col-form-label">Tax/VAT Number</label>
                                <div class="col-sm-9">
                                <input type="text" name="tax_vat" class="form-control" placeholder="Enter tax/VAT number">
                                </div>
                            </div>

                            <!-- First Name -->
                            <div class="row align-items-center mb-2">
                                <label class="col-sm-3 col-form-label">First Name</label>
                                <div class="col-sm-9">
                                <input type="text" name="fname" class="form-control" placeholder="Enter first name">
                                </div>
                            </div>

                            <!-- Last Name -->
                            <div class="row align-items-center mb-2">
                                <label class="col-sm-3 col-form-label">Last Name</label>
                                <div class="col-sm-9">
                                <input type="text" name="lname" class="form-control" placeholder="Enter last name">
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="row align-items-center mb-2">
                                <label class="col-sm-3 col-form-label">Address*</label>
                                <div class="col-sm-9">
                                <input type="text" name="address" class="form-control" placeholder="Enter address" required>
                                </div>
                            </div>

                            <!-- City -->
                            <div class="row align-items-center mb-2">
                                <label class="col-sm-3 col-form-label">City*</label>
                                <div class="col-sm-9">
                                <input type="text" name="city" class="form-control" placeholder="Enter city" required>
                                </div>
                            </div>

                            <!-- ZIP -->
                            <div class="row align-items-center mb-2">
                                <label class="col-sm-3 col-form-label">ZIP*</label>
                                <div class="col-sm-9">
                                <input type="text" name="zip" class="form-control" placeholder="Enter ZIP code" required>
                                </div>
                            </div>

                            <!-- Country -->
                                <div class="row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label">Country*</label>
                                    <div class="col-sm-9">
                                        <select name="country" id="country-select" class="form-control" required>
                                            <option value="">{{ $countries->isEmpty() ? 'No countries available' : 'Select Country' }}</option>
                                            @foreach($countries as $country)
                                                <option value="{{ $country->id }} {{ $country->name }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                                    {{ $country->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card shadow-sm p-4">
                                <h5 class="text-center mb-4">Payment Method</h5>
                                <!-- <div  id="card-element" class="form-control">
                                    
                                </div> -->
                                <div class="container mt-4">
                                    
                                    

                                    <!-- Card Number -->
                                    <div class="mb-3">
                                    <label for="card-number-element" class="form-label">Card Number*</label>
                                    <div id="card-number-element" class="form-control" required></div>
                                    <div id="cardNumber-error" class="text-danger small mt-1"></div>
                                    </div>

                                    <!-- Expiry + CVC -->
                                    <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="card-expiry-element" class="form-label">Expiry Date*</label>
                                        <div id="card-expiry-element" class="form-control" required></div>
                                        <div id="cardExpiry-error" class="text-danger small mt-1"></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="card-cvc-element" class="form-label">CVC*</label>
                                        <div id="card-cvc-element" class="form-control" required></div>
                                        <div id="cardCvc-error" class="text-danger small mt-1"></div>
                                    </div>
                                    </div>

                                    <!-- Name on Card -->
                                    <div class="mb-3">
                                    <label for="cardholder-name" class="form-label">Name on Card*</label>
                                    <input type="text" name="cardholder_name" id="cardholder-name" class="form-control" placeholder="Enter name on card" required>
                                    </div>
                                    <!-- Region -->
                                    <div class="mb-3" id="region">
                                    
                                    </div>
                                    <!-- Postal Code (optional) -->
                                    <div class="mb-3">
                                    <label for="card-postal-element" class="form-label">Postal Code*</label>
                                    <div id="card-postal-element" class="form-control" required></div>
                                    <div id="postalCode-error" class="text-danger small mt-1"></div>
                                    </div>
                                </div>  
                                
                                <button type="button" id="btn-confirmation" class="btn btn-primary" data-bs-toggle="modal">
                                    Pay
                                </button>    
                            </div>
                        </div>
                        </div>
                    
                </div>
            </div>
        </div>

        <!-- The Modal -->
        <div class="modal" id="paymentConfirmationModal" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header justify-content-center">
                        <h4 class="modal-title">💳 Payment Confirmation</h4>
                    </div>
                    <!-- Modal body -->
                    <div class="modal-body text-center">

                        <p class="mb-2">You are about to purchase:</p>

                        <div class="p-4 border rounded-3 bg-light shadow-sm mb-4">
                            <div class="fw-semibold fs-5 mb-1">{{ $selectedTariff->title }}</div>
                            <div class="fs-4 text-success fw-bold">
                                {{ number_format($selectedTariff->price_cents / 100, 2) }} {{ $selectedTariff->currency }}
                            </div>
                        </div>

                        <p class="text-muted small mb-0">
                            Please review your selection carefully before confirming your payment.
                        </p>
                    </div>

                    <!-- Modal footer -->
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">
                            Pay Now
                        </button>
                        <button id="btnCancel" type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div id="payment-success" class="text-center d-none">
        <div class="d-flex justify-content-center align-items-center flex-column">
            <div class="rounded-circle bg-success d-flex justify-content-center align-items-center"
                style="width:120px; height:120px;">
                <i class="bi bi-check-lg text-white" style="font-size:60px;"></i>
            </div>
            <h2 class="mt-4 text-success">Payment Received</h2>
        </div>
    </div>

    <div class="d-flex justify-content-between mt-4">

        @php
            $prev = ($currentStep ?? 2) - 1;
            $prevUrl = $prev >= 1 ? route('voting.create.step', ['step' => $prev]) : route('voting.realized');
    
            $nextStep = ($currentStep ?? 2) + 1;
            $nextBase = route('voting.create.step', ['step' => $nextStep]);
            $qs = request()->getQueryString();
            $nextUrl = $nextBase . ($qs ? ('?' . $qs) : '');
        @endphp

        <a href="{{ $prevUrl }}" class="btn btn-light">{{ $prev >= 1 ? 'Back' : 'Cancel' }}</a>

        {{-- Changed type to button so it doesn't submit --}}
        <button type="button" id="rewardNextBtn" class="btn btn-success" disabled>Next</button>
    </div>
<!-- <script>
    document.getElementById("btn-confirmation").addEventListener("click", function () {
        const form = document.getElementById("stripe-form");

        // Check HTML5 validity
        if (form.checkValidity()) {
            // Open modal only if valid
            const modal = new bootstrap.Modal(document.getElementById('paymentConfirmationModal'));
            modal.show();
        } else {
            // Show validation errors
            form.reportValidity();
        }
    });
</script> -->

<script src="https://js.stripe.com/v3/"></script>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@25.10.1/build/js/intlTelInput.min.js"></script>


<script type="text/javascript">

    const input = document.querySelector("#phone");
    const iti = window.intlTelInput(input, {
        initialCountry: "auto",
        utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@25.10.1/build/js/utils.js" // ensure utils are loaded
    });
    fetch("https://ipapi.co/country/")
    .then(res => res.text())
    .then(countryCode => {
        iti.setCountry(countryCode.toLowerCase());
    })

    var stripe = Stripe('{{ env("STRIPE_KEY") }}');
    var elements = stripe.elements();

    var cardNumber = elements.create('cardNumber');
    var cardExpiry = elements.create('cardExpiry');
    var cardCvc = elements.create('cardCvc');
    var postalCode = elements.create('postalCode');

    cardNumber.mount('#card-number-element');
    cardExpiry.mount('#card-expiry-element');
    cardCvc.mount('#card-cvc-element');
    postalCode.mount('#card-postal-element');

    // Track element validity
    let stripeErrors = {
        cardNumber: true, // assume invalid until validated
        cardExpiry: true,
        cardCvc: true,
        postalCode: true
    };

    // Utility to show error message
    function showStripeError(field, message) {
        const errorDiv = document.getElementById(field + '-error');
        if (errorDiv) {
            errorDiv.textContent = message || '';
        }
    }

    // Attach event listeners
    cardNumber.on('change', function(event) {
        if (event.empty) {
            stripeErrors.cardNumber = true;
            showStripeError('cardNumber', "Please enter a card number");
        } else if (event.error) {
            stripeErrors.cardNumber = true;
            showStripeError('cardNumber', event.error.message);
        } else {
            stripeErrors.cardNumber = false;
            showStripeError('cardNumber', "");
        }
    });

    cardExpiry.on('change', function(event) {
        if (event.empty) {
            stripeErrors.cardExpiry = true;
            showStripeError('cardExpiry', "Please enter expiry date");
        } else if (event.error) {
            stripeErrors.cardExpiry = true;
            showStripeError('cardExpiry', event.error.message);
        } else {
            stripeErrors.cardExpiry = false;
            showStripeError('cardExpiry', "");
        }
    });

    cardCvc.on('change', function(event) {
        if (event.empty) {
            stripeErrors.cardCvc = true;
            showStripeError('cardCvc', "Please enter CVC");
        } else if (event.error) {
            stripeErrors.cardCvc = true;
            showStripeError('cardCvc', event.error.message);
        } else {
            stripeErrors.cardCvc = false;
            showStripeError('cardCvc', "");
        }
    });

    postalCode.on('change', function(event) {
        if (event.empty) {
            stripeErrors.postalCode = true;
            showStripeError('postalCode', "Please enter postal code");
        } else if (event.error) {
            stripeErrors.postalCode = true;
            showStripeError('postalCode', event.error.message);
        } else {
            stripeErrors.postalCode = false;
            showStripeError('postalCode', "");
        }
    });

    document.getElementById("btn-confirmation").addEventListener("click", function () {
        const form = document.getElementById("stripe-form");

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        if (stripeErrors.cardNumber) {
            showStripeError('cardNumber', "Please enter a valid card number");
        }
        if (stripeErrors.cardExpiry) {
            showStripeError('cardExpiry', "Please enter card expiry date");
        }
        if (stripeErrors.cardCvc) {
            showStripeError('cardCvc', "Please enter CVC");
        }
        if (stripeErrors.postalCode) {
            showStripeError('postalCode', "Please enter postal code");
        }
        const hasStripeErrors = Object.values(stripeErrors).some(err => err === true);
        if (hasStripeErrors) {
            return;
        }
        const modal = new bootstrap.Modal(document.getElementById('paymentConfirmationModal'));
        modal.show();
    });

        let shippingAddressElement = null;

    $('#country-select').on('change', function () {
        let selectedCountry = $(this).val();
        let selectedCountryCode = selectedCountry.split(" ").slice(1).join(" "); 
        if (!selectedCountry) {
            return; 
        }

        // Create dropdown with only the selected country
        let dropdownHtml = `
            <label for="region-country" class="form-label">Country or Region</label>
            <select id="region-country" name="country" class="form-select">
                <option value="${selectedCountry}" selected>${selectedCountryCode}</option>
            </select>
        `;

        // Inject into #region
        $('#region').html(dropdownHtml);
        
        // if (shippingAddressElement) {
        //     shippingAddressElement.unmount();
        //     shippingAddressElement = null;
        // }
        // elements = stripe.elements();
        
        // shippingAddressElement = elements.create('address', {
        //     mode: 'billing',
        //     fields: {
        //         country: 'always',   // show country
        //     },
        //     defaultValues: {
        //         address: {
        //             country: selectedCountryCode
        //         }
        //     }
        // });

        // shippingAddressElement.mount('#region');
    });

    var nextUrl = @json($nextUrl);
    
    $(document).on('submit', '#stripe-form', function(e) {
    e.preventDefault();
     $("#btnCancel").prop("disabled", true);

    let rawNumber   = input.value; 
    if (rawNumber.startsWith("0")) {
        rawNumber = rawNumber.substring(1);
        } 
    let countryCode = iti.getSelectedCountryData().dialCode;
    console.log(rawNumber, countryCode);
    $('#phone').val(countryCode + rawNumber);


    let form = $('#stripe-form');
    let submitBtn = form.find('button[type="submit"]');

    submitBtn.html('<span class="spinner-border spinner-border-sm"></span> Processing...').prop('disabled', true);
    stripe.createToken(cardNumber).then(function(result) {
        if (result.error) {
            showToast(result.error.message, "error");
            resetFormState(); 
        } else {
            $('#stripe-token').val(result.token.id);
            $.ajax({
                type: 'POST',
                url: form.attr('action'),
                data: form.serialize(),
                success: function (response) {
                    showToast(response.message || 'Payment successful!', "success");
                    
                    $('#paymentConfirmationModal').modal('hide');

                     $("#payment-form-container")
                        .removeClass("d-block")
                        .addClass("d-none");

                    $("#payment-success")
                        .removeClass("d-none")
                        .addClass("d-block");
                     $("#rewardNextBtn").prop("disabled", false);
                     

                },
                error: function (xhr) {
                    $('#paymentConfirmationModal').modal('hide');
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        Object.values(errors).forEach(function(errorArray) {
                            errorArray.forEach(function(error) {
                                showToast(error, "error");
                            });
                        });
                    } else {
                        showToast('Payment failed. Please try again.', "error");
                    }
                    resetFormState(); 
                }
            });
        }
    });
    
    function resetFormState() {
        let form = $('#stripe-form');
        let submitBtn = form.find('button[type="submit"]');
        form.find('input, select, textarea, button').prop('disabled', false);
        submitBtn.removeClass('btn-success').addClass('btn-primary').html('Pay');
    }
});

$(document).on('click', '#rewardNextBtn', function() {
    window.location.href = nextUrl;
});

  function showToast(message, type = "info") {
        Toastify({
            text: message,
            duration: 6000,
            close: true,
            gravity: "top", 
            position: "right", 
            backgroundColor: type === "success" ? "#2E8B57" :
                                type === "error" ? "#B22222" :
                                type === "warning" ? "orange" : "#333",
            stopOnFocus: true, 
        }).showToast();
    }
    
    function onCaptchaSuccess(response) {
        document.getElementById('cf-turnstile-response').value = response;
        
        document.getElementById('captcha-widget').style.display = 'none';
        document.getElementById('captcha-success').style.display = 'block';
        setTimeout(function() {
            document.getElementById('captcha-container').style.display = 'none';
            document.getElementById('payment-form-wrapper').style.display = 'block';
        }, 1000);
    }
    /* $(document).on('change', '#country-select', function () {
        let countryName = $("#country-select option:selected").text();
        let regionDiv = $("#region");

        regionDiv.empty(); // clear previous content

        // Option 1: just show selected country as readonly
        regionDiv.html(`
            <label for="region-input" class="form-label">Region/State</label>
            <input type="text" id="region-input" name="region" value=${countryName}
                class="form-control" placeholder="Enter region/state" readonly />
        `);

    }); */

</script>
