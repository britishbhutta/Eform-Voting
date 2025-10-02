<x-app-layout>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
        <style>
            body { background-color: #f8f9fa; }
            .voting-card { max-width: 600px; margin: 2rem auto; }
            .voting-header { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; }
            .success-icon { font-size: 4rem; color: #28a745; }
        </style>
    @endpush


    Welcome...!

    <div class="container py-4">
        <div class="card">
            <div class="card-body text-center">
                @if($votingEventVotes->isEmpty())
                    <h4>No Voting History Available</h4>
                @else
                    <div class="container my-5">
                        <h3 class="mb-4">Voting History</h3>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered align-middle table-green">
                            <thead>
                                <tr>
                                <th>Date</th>
                                <th>Name of voting form</th>
                                <th>Question</th>
                                <th>Voted Option</th>
                                <th>Reward</th>
                                <th>View</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($votingEventVotes as $votingEventVote)
                                    <tr>
                                        <td>{{ $votingEventVote->created_at }}</td>
                                        <td>{{ $votingEventVote->votingEvent->title }}</td>
                                        <td>{{ $votingEventVote->votingEvent->question }}</td>
                                        <td>{{ $votingEventVote->option->option_text }}</td>
                                        <td>
                                            <a href="#"
                                                data-bs-toggle="modal"
                                                data-bs-target="#rewardDetail"
                                                data-name="{{ $votingEventVote->votingEvent->booking->reward->name }}"
                                                data-description="{{ $votingEventVote->votingEvent->booking->reward->description }}"
                                                data-image="{{ $votingEventVote->votingEvent->booking->reward->image }}"
                                            >
                                            {{ $votingEventVote->votingEvent->booking->reward->name }} 
                                            </a>
                                            
                                        </td>                                      
                                        <td>
                                            <a href="#" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#voteDetail"
                                                data-created_at="{{ $votingEventVote->created_at }}"
                                                data-title="{{ $votingEventVote->votingEvent->title }}"
                                                data-question="{{ $votingEventVote->votingEvent->question }}"
                                                data-option="{{ $votingEventVote->option->option_text }}"
                                                >
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor"
                                                    class="text-gray-600 hover:text-blue-600"
                                                    width="20" height="20">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 
                                                        12 4.5c4.639 0 8.577 3.01 9.964 7.183.07.207.07.431 0 
                                                        .639C20.577 16.49 16.639 19.5 12 19.5c-4.64 0-8.577-3.01-9.964-7.178z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                <!-- Vote Modal -->
                                <div class="modal fade" id="voteDetail" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="container">
                                                <div class="modal-header">
                                                </div>
                                                
                                                <div class="modal-body">
                                                    <div class="voting-card">
                                                        <div class="card voting-header">
                                                            <div class="card-body text-center">
                                                                <h2 class="card-title mb-2"><span id="modal-title"></span></h2>
                                                                <p class="card-text mb-0"><span id="modal-question"></span></p>
                                                            </div>
                                                        </div>

                                                        <div class="card mt-3">
                                                            <div class="card-body text-center">
                                                                <div class="mb-4">
                                                                    <div class="success-icon mb-3">✓</div>
                                                                    <h4 class="text-success">You have Casted the Vote.</h4>
                                                                    <p class="text-muted">Thank you for participating in this voting event.</p>
                                                                </div>
                                                                
                                                                <div class="alert alert-success">
                                                                    <strong>Your Selection:</strong><br>
                                                                    <h5><span id="modal-option"></span></h5>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Reward Modal -->
                                <div class="modal fade" id="rewardDetail" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="container">
                                                <div class="modal-header">
                                                </div>
                                                
                                                <div class="modal-body">
                                                    <div class="voting-card">
                                                        <div class="card voting-header">
                                                            <div class="card-body text-center">
                                                                <h2 class="card-title mb-2">Reward</h2>
                                                                <h2 class="card-title mb-2"><span id="modal-name"></span></h2>
                                                                
                                                            </div>
                                                        </div>

                                                        <div class="card mt-3">
                                                            <div class="card-body text-center">
                                                                <div class="mb-4">
                                                                    <p class="card-text mb-4"><span id="modal-description"></span></p>
                                                                    <p class="card-text mb-0">
                                                                        <a id="modal-download-link" href="#" download title="Click on image to download the Reward">
                                                                            <img id="modal-image" src="" alt="Vote image" style="max-width: 100%; height: auto;">
                                                                        </a>
                                                                    </p>
                                                                    <p class="card-text mb-4">Click on Image to download</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const voteDetailModal = document.getElementById('voteDetail');
                voteDetailModal.addEventListener('show.bs.modal', function (event) {
                    let button = event.relatedTarget;

                    // Get data attributes
                    let createdAt = button.getAttribute('data-created_at');
                    let title = button.getAttribute('data-title');
                    let question = button.getAttribute('data-question');
                    let option = button.getAttribute('data-option');
                    
                    // Insert into modal
                    document.getElementById('modal-title').textContent = title;
                    document.getElementById('modal-question').textContent = question;
                    document.getElementById('modal-option').textContent = option;
                    
                });
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const rewardModal = document.getElementById('rewardDetail');
                rewardModal.addEventListener('show.bs.modal', function (event) {
                    let button = event.relatedTarget;

                    // Get data attributes
                    let name = button.getAttribute('data-name');
                    let description = button.getAttribute('data-description');
                    let image = button.getAttribute('data-image');
                    
                    // Insert into modal
                    document.getElementById('modal-name').textContent = name;
                    document.getElementById('modal-description').textContent = description;
                    

                    // Set image path
                    const imagePath = "{{ asset('/') }}storage/" + image;  // your image path
                    document.getElementById('modal-image').src = imagePath;

                    // Make it downloadable
                    const downloadLink = document.getElementById('modal-download-link');
                    downloadLink.href = imagePath;
                    downloadLink.download = "reward.png"; // you can set default filename
                });
            });
        </script>
    @endpush
</x-app-layout>