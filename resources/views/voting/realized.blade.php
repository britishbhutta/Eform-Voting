{{-- resources/views/voting/realized.blade.php --}}
<x-app-layout>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    @endpush

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
                                <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bookings as $booking)
                                    @if($booking->is_completed == '1')
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
                                            <td>-</td>
                                            <td>{{ $booking->tariff->title }}</td>
                                            <td>Completed</td>
                                        </tr>
                                    @else
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
                                            <td>-</td>
                                            <td>{{ $booking->tariff->title }}</td>
                                            <td>
                                                <form action="{{ route('voting.set', $booking->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary btn-sm">Incomplete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endif
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
