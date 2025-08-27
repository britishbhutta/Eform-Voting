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
                            <table class="table table-striped table-bordered align-middle">
                            <thead class="table-dark">
                                <tr>
                                <th>Date</th>
                                <th>Name of voting form</th>
                                <th>Result</th>
                                <th>File with email</th>
                                <th>Tariff</th>
                                <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bookings as $booking)
                                    @if($booking->is_completed == '1')
                                        <tr>
                                            <td>8.7.2024</td>
                                            <td>Player of the match 1st round</td>
                                            <td>Rolando 80%, Messi 20%</td>
                                            <td><a href="play1.csv" download>play1.csv</a></td>
                                            <td>{{ $booking->tariff->title }}</td>
                                            <td>Completed</td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
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
