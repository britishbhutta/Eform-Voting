{{-- resources/views/partials/voting-tabs.blade.php --}}
@php
    $currentStep = $currentStep ?? 1;
    $stepNames = $stepNames ?? [
        1 => 'Choose Tariff',
        2 => 'Personal info & payments',
        3 => 'Insert reward',
        4 => 'Detail of event',
        5 => 'Creation of form',
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
            <a href="{{ route('voting.create.step', ['step' => 1] + (request()->query() ? request()->query() : [])) }}"
               class="tab-btn {{ $isCreateRoute ? 'active-tab' : '' }}">
                Create a new voting form
            </a>
        </li>
        <li>
            <a href="{{ route('voting.realized') }}"
               class="tab-btn {{ $isRealized ? 'active-tab' : '' }}">
                Realized voting
            </a>
        </li>
    </ul>
</div>
