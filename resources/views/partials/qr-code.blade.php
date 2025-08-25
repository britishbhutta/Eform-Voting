
<div class="p-4">


    @if($selectedTariff)
        <div class="mb-3 ">
            <p class="mb-0 text-success">
                <strong>Selected tariff:</strong>  
                {{ $selectedTariff->title }}  
                ({{ number_format($selectedTariff->price_cents / 100, 2) }} {{ $selectedTariff->currency }})
            </p>
        </div>
    @endif
 
    <div class="d-flex justify-content-center align-items-center flex-column text-center">
        <div class="mb-3">
            <a target="_blank" href="https://cricket.nwlogics.com/">
               https://cricket.nwlogics.com/
            </a>
        </div>
        <div id="qrcode" class="mt-3"></div>
    </div>
</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    new QRCode(document.getElementById("qrcode"), {
        text: "https://cricket.nwlogics.com/",
        width: 120,
        height: 120
    });
</script>
