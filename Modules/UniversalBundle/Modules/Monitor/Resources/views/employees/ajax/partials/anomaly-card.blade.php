@php
    $positiveSignals = collect($positiveSignals ?? []);
    $attentionItems = collect($attentionItems ?? []);
@endphp

<div class="card bg-white border-0 b-shadow-4 mb-3">
    <div class="card-header bg-white border-bottom-grey p-20">
        <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Attention & Anomalies</h4>
        <p class="f-12 text-lightest mb-0 mt-1">Signals that may need a quick manager review.</p>
    </div>
    <div class="card-body p-0">
        <div class="row no-gutters">
            <div class="col-lg-6 p-20 border-right-grey border-bottom-grey border-bottom-lg-0">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Attention</h4>
                    <span class="badge badge-warning">{{ $attentionItems->count() }} items</span>
                </div>
                @forelse ($attentionItems as $item)
                    <div class="bg-additional-grey border-left-warning rounded p-3 mb-2 f-14 text-dark-grey">{{ $item['label'] }}</div>
                @empty
                    <div class="bg-grey rounded p-3 f-14 text-lightest text-center">No attention items detected.</div>
                @endforelse
            </div>
            <div class="col-lg-6 p-20">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Positive Signals</h4>
                    <span class="badge badge-success">{{ $positiveSignals->count() }} items</span>
                </div>
                @forelse ($positiveSignals as $item)
                    <div class="bg-additional-grey border-left-success rounded p-3 mb-2 f-14 text-dark-grey">{{ $item['label'] }}</div>
                @empty
                    <div class="bg-grey rounded p-3 f-14 text-lightest text-center">No positive signals detected yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
