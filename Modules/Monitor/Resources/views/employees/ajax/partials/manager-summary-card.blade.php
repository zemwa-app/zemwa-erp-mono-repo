@php
    $summaryText = $managerSummary['text'] ?? 'No summary available.';
    $delta = (float) ($managerSummary['delta'] ?? 0);
    $currentApp = $managerSummary['current_app'] ?? null;
    $deltaBadge = $delta >= 0 ? 'badge-success' : 'badge-danger';
@endphp

<div class="card bg-white border-0 b-shadow-4 mb-3">
    <div class="card-header bg-white border-bottom-grey p-20">
        <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Manager Summary</h4>
        <p class="f-12 text-lightest mb-0 mt-1">AI-style readout of what matters right now.</p>
    </div>
    <div class="card-body p-20">
        <div class="bg-grey rounded p-20 mb-3">
            <p class="f-14 text-dark-grey mb-0">“{{ $summaryText }}”</p>
        </div>
        <div class="d-flex flex-wrap mb-3">
            <span class="badge {{ $deltaBadge }} mr-2 mb-2">
                {{ $delta >= 0 ? '+' : '' }}{{ number_format($delta, 1) }} vs team average
            </span>
            @if (!empty($currentApp))
                <span class="badge badge-secondary mr-2 mb-2">
                    Current focus: {{ \Illuminate\Support\Str::limit($currentApp, 24) }}
                </span>
            @endif
            @if (!empty($summaryApps))
                <span class="badge badge-secondary mb-2">
                    Most used: {{ \Illuminate\Support\Str::limit($summaryApps, 28) }}
                </span>
            @endif
        </div>
        <div class="row mb-3">
            @foreach ([
                ['label' => 'Positive', 'value' => number_format($positiveCount ?? 0)],
                ['label' => 'Alerts', 'value' => number_format($attentionCount ?? 0)],
                ['label' => 'Today', 'value' => number_format($score, 1) . '%'],
            ] as $metric)
                <div class="col-4">
                    <div class="bg-grey rounded p-3 text-center">
                        <div class="f-11 text-lightest text-uppercase">{{ $metric['label'] }}</div>
                        <div class="f-16 f-w-500 text-darkest-grey mt-1">{{ $metric['value'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="bg-grey rounded p-3 f-12 text-dark-grey">
            <span class="f-w-500 text-darkest-grey">Trend:</span> {{ $trendComparison['label'] ?? '+0.0% vs last week' }}
        </div>
    </div>
</div>
