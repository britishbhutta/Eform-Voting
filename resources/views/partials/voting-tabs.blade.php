{{-- resources/views/partials/voting-tabs.blade.php --}}
@php
    $currentStep = $currentStep ?? 2;
    $stepNames = $stepNames ?? [
        1 => 'Personal Info',
        2 => 'Choose Tariff',
        3 => 'Insert Reward',
        4 => 'Detail Of Event',
        5 => 'Payment',
        6 => 'Creation Of Form',
    ];

    $isCreateRoute = request()->routeIs('voting.create') || request()->routeIs('voting.create.step');
    $isRealized = request()->routeIs('voting.realized');
@endphp

<div class="d-flex align-items-center justify-content-between mb-3">
    <div class="d-flex align-items-center gap-3">
        {{-- Title area --}}
        @if($isCreateRoute)
            <div>
                <h4 class="mb-0">{{ $stepNames[$currentStep] }}</h4>
            </div>
        @endif
    </div>

    {{-- Tabs --}}
    <ul class="voting-tabs">
        <li>
            <!-- <a href="{{ route('voting.create.step', ['step' => 1] + (request()->query() ? request()->query() : [])) }}" -->
            <a href="{{ route('create.new.voting.form') }}"
               class="tab-btn {{ $isCreateRoute ? 'active-tab' : '' }}">
                Create A New Voting Form
            </a>
        </li>
        <li>
            <a href="{{ route('voting.realized') }}"
               class="tab-btn {{ $isRealized ? 'active-tab' : '' }}">
                Realized Voting
            </a>
        </li>
    </ul>
</div>
