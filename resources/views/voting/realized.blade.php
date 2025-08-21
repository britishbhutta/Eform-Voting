{{-- resources/views/voting/realized.blade.php --}}
<x-app-layout>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    @endpush

    <div class="container py-4">
        @include('partials.voting-tabs')

        <div class="card">
            <div class="card-body text-center">
                @if(!empty($votings) && count($votings))
                    <div class="row">
                        @foreach($votings as $v)
                            <div class="col-md-6 mb-3">
                                <div class="border p-3">
                                    <h5>{{ $v->title }}</h5>
                                    <p class="mb-1 text-muted">Created: {{ $v->created_at->format('Y-m-d') }}</p>
                                    <a href="#" class="btn btn-sm btn-primary">View</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
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
                                <th>Tarif</th>
                                <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                <td>8.7.2024</td>
                                <td>Player of the match 1st round</td>
                                <td>Rolando 80%, Messi 20%</td>
                                <td><a href="play1.csv" download>play1.csv</a></td>
                                <td>extra</td>
                                <td>Completed</td>
                                </tr>
                                <tr>
                                <td>9.7.2024</td>
                                <td>Player of the match 2st round</td>
                                <td>Rolando 60%, Messi 40%</td>
                                <td><a href="play2.csv" download>play2.csv</a></td>
                                <td>extra</td>
                                <td>Completed</td>
                                </tr>
                            </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
