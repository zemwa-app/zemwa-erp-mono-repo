@php
    $rows = collect($flatUrls ?? []);
@endphp

<div class="card bg-white border-0 b-shadow-4 mb-3">
    <div class="card-header bg-white border-bottom-grey p-20">
        <div class="d-flex align-items-start justify-content-between">
            <div>
                <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Detailed URL Log</h4>
                <p class="f-12 text-lightest mb-0 mt-1">Collapsed by default for investigation only.</p>
            </div>
            <button type="button" class="btn btn-primary btn-sm rounded f-12" data-url-toggle>
                <i class="fa fa-plus mr-1" aria-hidden="true"></i>
                <span>Expand detailed URLs</span>
            </button>
        </div>
    </div>
    <div class="d-none p-20" data-url-panel>
        <div class="monitor-search-panel table-responsive">
            @include('monitor::partials.table-search', [
                'id' => 'monitor-websites-search',
                'placeholder' => __('monitor::app.searchDomainOrUrl'),
            ])
            <table class="table table-hover w-100 monitor-searchable-table">
                <thead>
                    <tr>
                        <th>URL</th>
                        <th>Domain</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Start Time</th>
                        <th>Duration</th>
                        <th>Productivity Classification</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        @php
                            $badgeClass = match ($row['category'] ?? null) {
                                'productive' => 'badge-success',
                                'unproductive' => 'badge-danger',
                                default => 'badge-warning',
                            };
                        @endphp
                        <tr class="monitor-search-row" data-search="{{ $row['search_haystack'] }}">
                            <td style="max-width:260px;">
                                <a href="{{ $row['url'] }}" target="_blank" rel="noopener noreferrer" class="text-primary text-truncate d-inline-block" style="max-width:240px;">
                                    {{ \Illuminate\Support\Str::limit($row['url'], 64) }}
                                </a>
                            </td>
                            <td class="f-14 text-dark-grey">{{ $row['domain'] }}</td>
                            <td class="f-14 text-dark-grey" style="max-width:240px;" title="{{ $row['title'] }}">{{ $row['title'] }}</td>
                            <td><span class="badge {{ $badgeClass }}">{{ $row['category_label'] }}</span></td>
                            <td class="text-nowrap f-14 text-dark-grey">{{ $row['started_at'] }}</td>
                            <td class="text-nowrap f-w-500 text-darkest-grey">{{ $row['duration_label'] }}</td>
                            <td class="f-14 text-dark-grey">{{ $row['productivity_label'] }}</td>
                            <td class="text-right">
                                <a href="{{ $row['url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-secondary btn-sm rounded f-12">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center f-14 text-lightest p-20">No detailed URL logs available.</td>
                        </tr>
                    @endforelse
                    @if ($rows->count() > 0)
                        <tr class="monitor-search-empty d-none">
                            <td colspan="8" class="text-center f-14 text-lightest p-20">No records match your search.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
