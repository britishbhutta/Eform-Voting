{{-- resources/views/voting/realized.blade.php --}}
<x-app-layout>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    @endpush

    <div class="container py-4">
        @include('partials.voting-tabs')

        <div class="card">
            <div class="card-body text-center py-5">
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
                    
                    <h4>No voting forms yet</h4>
                    <p class="text-muted">Click <strong>Create a new voting form</strong> to start building your first poll.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
