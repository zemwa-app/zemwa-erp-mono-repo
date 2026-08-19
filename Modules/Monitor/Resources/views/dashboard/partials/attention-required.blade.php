@php
    $alerts = array_slice($dashboard['attention_required'] ?? [], 0, 5);
    $count = count($alerts);
@endphp

<x-cards.data class="mb-0" title="Attention Required">
    <x-slot name="action">
        <a href="{{ route('monitor.reports.index') }}" class="btn btn-primary btn-sm">
            Review All
        </a>
    </x-slot>
    <p class="f-12 text-lightest mb-3">Employees requiring manager review right now</p>

    <div>
        @forelse ($alerts as $alert)
            <div class="mb-3">
                @include('monitor::dashboard.partials.attention-employee-item', $alert)
            </div>
        @empty
            <div class="card bg-additional-grey border-0 p-20 text-center">
                <p class="f-14 f-w-500 text-darkest-grey mb-1">No employees need review</p>
                <p class="f-12 text-lightest mb-0">Everyone is within acceptable activity thresholds right now.</p>
            </div>
        @endforelse
    </div>
</x-cards.data>
