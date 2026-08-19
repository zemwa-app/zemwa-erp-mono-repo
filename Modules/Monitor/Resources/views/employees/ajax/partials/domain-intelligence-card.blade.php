@php
    $items = collect($websiteRows ?? []);
@endphp

<div class="card bg-white border-0 b-shadow-4 mb-3">
    <div class="card-header bg-white border-bottom-grey p-20">
        <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Domain Intelligence</h4>
        <p class="f-12 text-lightest mb-0 mt-1">Domains first, URLs only when needed.</p>
    </div>
    <div class="card-body p-0">
        @forelse ($items as $index => $site)
            @php
                $domainId = 'domain-details-' . $index;
                $urls = collect($site['sessions'] ?? [])->pluck('url')->filter()->unique()->values();
            @endphp
            <div class="p-20 border-bottom-grey">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="d-flex align-items-start">
                        @include('monitor::analytics.partials.app-icon', [
                            'size' => 34,
                            'iconUrl' => $site['icon_url'] ?? null,
                            'letterAvatar' => $site['letter_avatar'] ?? null,
                            'alt' => $site['display_name'] ?? '',
                        ])
                        <div class="ml-3">
                            <div class="f-14 f-w-500 text-darkest-grey">{{ $site['display_name'] ?? 'Unknown' }}</div>
                            <div class="d-flex flex-wrap mt-2">
                                <span class="badge badge-secondary mr-2 mb-1">Total Time: {{ $site['duration_label'] ?? '0m' }}</span>
                                <span class="badge badge-secondary mr-2 mb-1">Visits: {{ number_format((int) ($site['visit_count'] ?? 0)) }}</span>
                                <span class="badge badge-secondary mr-2 mb-1">Unique Pages: {{ number_format((int) ($site['unique_pages'] ?? 0)) }}</span>
                                <span class="badge badge-secondary mb-1">Last Visit: {{ $site['last_visit_label'] ?? '—' }}</span>
                            </div>
                        </div>
                    </div>
                    <button type="button"
                        class="btn btn-secondary btn-sm rounded f-12"
                        data-domain-toggle="#{{ $domainId }}"
                        aria-expanded="false">
                        <i class="fa fa-plus mr-1" aria-hidden="true"></i>
                        <span>Expand</span>
                    </button>
                </div>

                <div id="{{ $domainId }}" class="d-none bg-grey rounded p-20 mt-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="f-12 f-w-500 text-lightest text-uppercase">URLs</div>
                        <div class="f-12 text-lightest">{{ $urls->count() }} unique pages</div>
                    </div>
                    @foreach ($site['sessions'] ?? [] as $session)
                        <div class="d-flex align-items-start justify-content-between bg-white border-grey rounded p-3 mb-2">
                            <div class="flex-grow-1 mr-3">
                                <div class="text-truncate f-14 f-w-500 text-darkest-grey">{{ $session['url'] ?? '—' }}</div>
                                <div class="f-12 text-lightest mt-1">{{ $session['window_title'] ?? '—' }}</div>
                            </div>
                            <div class="d-flex align-items-center f-12 text-lightest">
                                <span class="mr-2">{{ $session['duration_label'] ?? '0m' }}</span>
                                <a href="{{ $session['url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm rounded f-12">Open</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="p-20 text-center f-14 text-lightest">No domain intelligence available.</div>
        @endforelse
    </div>
</div>
