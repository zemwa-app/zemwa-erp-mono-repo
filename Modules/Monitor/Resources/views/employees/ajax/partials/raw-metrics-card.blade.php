<div class="card bg-white border-0 b-shadow-4 mb-3">
    <div class="card-header bg-white border-bottom-grey p-20">
        <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Raw Metrics</h4>
        <p class="f-12 text-lightest mb-0 mt-1">Supporting counters kept secondary to the intelligence above.</p>
    </div>
    <div class="card-body p-20">
        <div class="row">
            @foreach ([
                ['label' => 'Keystrokes', 'value' => $overview['keystrokes'] ?? '0'],
                ['label' => 'Mouse Clicks', 'value' => $overview['mouse_clicks'] ?? '0'],
                ['label' => 'Mouse Distance', 'value' => $overview['mouse_distance'] ?? '0 px'],
                ['label' => 'Scroll Events', 'value' => $overview['scroll_events'] ?? '0'],
            ] as $metric)
                <div class="col-sm-6 col-xl-3 mb-3">
                    <div class="bg-grey rounded p-3">
                        <div class="f-11 text-lightest text-uppercase">{{ $metric['label'] }}</div>
                        <div class="f-14 f-w-500 text-darkest-grey mt-1">{{ $metric['value'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
