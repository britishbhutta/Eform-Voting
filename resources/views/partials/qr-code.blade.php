<div class="p-4">
    <h5 class="mb-4 text-center">Voting Event Created Successfully!</h5>
 
    @if($selectedTariff)
        <div class="mb-3 text-center">
            <p class="mb-0 text-success">
                <strong>Selected tariff:</strong>  
                {{ $selectedTariff->title }}  
                ({{ number_format($selectedTariff->price_cents / 100, 2) }} {{ $selectedTariff->currency }})
            </p>
        </div>
    @endif
 
    @if($votingEvent)
        <div class="mb-4">
            <div class="row">
                <div class="col-md-6">
                    <h6>Voting Event Details:</h6>
                    <p><strong>Title:</strong> {{ $votingEvent->title }}</p>
                    <p><strong>Question:</strong> {{ $votingEvent->question }}</p>
                    <p><strong>Start Date:</strong> {{ $votingEvent->start_at ? Carbon\Carbon::parse($votingEvent->start_at)->format('M d, Y H:i') : 'Not set' }}</p>
                    <p><strong>End Date:</strong> {{ $votingEvent->end_at ? Carbon\Carbon::parse($votingEvent->end_at)->format('M d, Y H:i') : 'Not set' }}</p>
                </div>
                <div class="col-md-6 text-center">
                    <h6>QR Code for Voters</h6>
                    <p class="text-muted small">Scan this QR code to access the voting form</p>
                    <div id="qrcode" class="mt-3 d-inline-block"></div>
                    <div class="mt-3">
                        <div class="mt-2">
                            <button class="btn btn-primary btn-sm" type="button" id="download-qr-btn">Download QR Code</button>
                        </div>
                        @php($publicUrl = route('voting.public', ['token' => $votingEvent->token]))
                        <div class="input-group input-group-sm mt-3" style="max-width: 100%;">
                            <input type="text" class="form-control" id="voting-url" value="{{ $publicUrl }}" readonly>
                            <button class="btn btn-outline-secondary" type="button" id="copy-voting-url">Copy</button>
                        </div>
                        
                       
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-warning">
            No voting event found. Please complete the previous steps first.
        </div>
    @endif
</div>
 
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    @if($votingEvent)
        new QRCode(document.getElementById("qrcode"), {
            text: "{{ route('voting.public', ['token' => $votingEvent->token]) }}",
            width: 200,
            height: 200,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });
 
        document.getElementById('copy-voting-url').addEventListener('click', function() {
            const input = document.getElementById('voting-url');
            input.select();
            input.setSelectionRange(0, 99999);
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(input.value).then(function() {
                    showCopyFeedback();
                }).catch(function() {
                    document.execCommand('copy');
                    showCopyFeedback();
                });
            } else {
                document.execCommand('copy');
                showCopyFeedback();
            }
        });
 
        document.getElementById('download-qr-btn').addEventListener('click', function() {
            const container = document.getElementById('qrcode');
            const canvas = container.querySelector('canvas');
            const img = container.querySelector('img');
 
            let dataUrl = null;
            if (canvas) {
                dataUrl = canvas.toDataURL('image/png');
            } else if (img && img.src) {
                dataUrl = img.src;
            }
 
            if (!dataUrl) {
                alert('Unable to export QR code. Please try a different browser.');
                return;
            }
 
            const link = document.createElement('a');
            link.href = dataUrl;
            link.download = 'voting-qr.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
 
        function showCopyFeedback() {
            const btn = document.getElementById('copy-voting-url');
            const original = btn.textContent;
            btn.textContent = 'Copied!';
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-success');
            setTimeout(function() {
                btn.textContent = original;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-secondary');
            }, 1500);
        }
    @endif
</script>