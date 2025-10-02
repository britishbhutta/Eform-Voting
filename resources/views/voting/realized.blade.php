{{-- resources/views/voting/realized.blade.php --}}
<x-app-layout>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    @endpush
    @if(session('error'))
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                Toastify({
                    text: "{{ session('error') }}",
                    duration: 3000, // 5 seconds
                    gravity: "top", // `top` or `bottom`
                    position: "right", // `left`, `center` or `right`
                    backgroundColor: "linear-gradient(to right, #ff5f6d, #ffc371)", // red/orange for error
                    stopOnFocus: true, // pause on hover
                    close: true
                }).showToast();
            });
        </script>
    @endif


    <div class="container py-4">
        @include('partials.voting-tabs')
        <div class="card">
            <div class="card-body text-center">
                @if($bookings->isEmpty())
                    <h4>No voting forms yet</h4>
                    <p class="text-muted">Click <strong>Create A New Voting Form</strong>To Start Building Your First Poll.</p>
                @else
                    <div class="container my-5">
                        <h3 class="mb-4">Voting Results</h3>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered align-middle table-green">
                            <thead>
                                <tr>
                                <th>Date</th>
                                <th>Name of voting form</th>
                                <th>Vote Cast</th>
                                <th>Results (%)</th>
                                <th>File with email</th>
                                <th>Tariff</th>
                                <th>QR</th>
                                <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bookings as $booking)
                                    <tr>
                                        <td>{{ optional($booking->created_at)->format('d.m.Y') ?? '-' }}</td>
                                        @php
                                            $votingEvent = \App\Models\VotingEvent::where('booking_id', $booking->id)->first();
                                            $purchasedTariff = \App\Models\PurchasedTariff::where('booking_id', $booking->id)->first();
                                            $options = $votingEvent ? \App\Models\VotingEventOption::where('voting_event_id', $votingEvent->id)->get() : collect();
                                            $totalVotes = (int) ($purchasedTariff->total_votes ?? 0);
                                        @endphp
                                        <td>{{ $votingEvent?->title ?? '-' }}</td>
                                        <td>{{ $purchasedTariff?->votes_count ?? 0 }}</td>
                                        <td>
                                            @if($options->isNotEmpty())
                                                @php
                                                    $percentages = [];
                                                    foreach ($options as $opt) {
                                                        $percentages[] = $totalVotes > 0 ? round(($opt->votes_count / $totalVotes) * 100) : 0;
                                                    }
                                                    $maxPct = count($percentages) ? max($percentages) : 0;

                                                    $partsHtml = [];
                                                    foreach ($options as $index => $opt) {
                                                        $pct = $percentages[$index] ?? 0;
                                                        $isWinner = ($pct === $maxPct) && ($maxPct > 0);
                                                        $class = $isWinner ? 'text-danger' : '';
                                                        $style = $isWinner ? 'font-weight:900; font-size:1.3em; color:#dc3545 !important;' : '';
                                                        $partsHtml[] = '<span class="' . $class . '" style="' . $style . '">' . e($opt->option_text) . ' <strong>' . $pct . '%</strong></span>';
                                                    }
                                                @endphp
                                                {!! implode(', ', $partsHtml) !!}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('voting.event.emails',[$votingEvent->id]) }}">Emails.CSV</a>
                                        </td>
                                        <td>{{ $booking->tariff->title }}</td>
                                        <td>
                                            @if($votingEvent)
                                                @php $publicUrl = route('voting.public', ['token' => $votingEvent->token]); @endphp
                                                <button type="button"
                                                        class="btn btn-outline-secondary btn-sm open-qr-modal"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#qrModal"
                                                        data-url="{{ $publicUrl }}"
                                                        data-title="{{ $votingEvent->title }}">
                                                    Download QR
                                                </button>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        @if($booking->is_completed == '1')
                                            <td>Completed</td>
                                        @else
                                            <td>
                                                <form action="{{ route('voting.set', $booking->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary btn-sm">Incomplete</button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

{{-- QR Modal --}}
<div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qrModalLabel">Voting Link QR</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div id="qrCanvasWrapper" class="mb-3"></div>
                <div class="input-group">
                    <input type="text" id="qrLinkInput" class="form-control" readonly>
                    <button class="btn btn-outline-secondary" type="button" id="copyLinkBtn">Copy</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <a id="downloadQrBtn" class="btn btn-primary" download="voting-qr.png">Download PNG</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.1/build/qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalEl = document.getElementById('qrModal');
            const qrWrapper = document.getElementById('qrCanvasWrapper');
            const linkInput = document.getElementById('qrLinkInput');
            const downloadBtn = document.getElementById('downloadQrBtn');
            const copyBtn = document.getElementById('copyLinkBtn');

            document.querySelectorAll('.open-qr-modal').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const url = this.getAttribute('data-url');
                    const title = this.getAttribute('data-title') || 'Voting Link QR';
                    document.getElementById('qrModalLabel').innerText = title;
                    linkInput.value = url;
                    qrWrapper.innerHTML = '';
                    const canvas = document.createElement('canvas');
                    qrWrapper.appendChild(canvas);
                    QRCode.toCanvas(canvas, url, { width: 240, margin: 2 }, function (error) {
                        if (error) console.error(error);
                        try {
                            downloadBtn.href = canvas.toDataURL('image/png');
                        } catch (e) {
                            downloadBtn.removeAttribute('href');
                        }
                    });
                });
            });

            copyBtn.addEventListener('click', function () {
                linkInput.select();
                linkInput.setSelectionRange(0, 99999);
                try {
                    document.execCommand('copy');
                    copyBtn.innerText = 'Copied!';
                    setTimeout(() => copyBtn.innerText = 'Copy', 1200);
                } catch (e) {}
            });
        });
    </script>
</div>
