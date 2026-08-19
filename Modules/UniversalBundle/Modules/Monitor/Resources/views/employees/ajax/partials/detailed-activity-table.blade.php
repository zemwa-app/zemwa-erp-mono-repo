@php
    $rows = collect($logs ?? []);
@endphp

<div class="card bg-white border-0 b-shadow-4 mb-3">
    <div class="card-header bg-white border-bottom-grey p-20">
        <div class="d-flex align-items-start justify-content-between">
            <div>
                <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Detailed Activity Log</h4>
                <p class="f-12 text-lightest mb-0 mt-1">Collapsed by default for investigation only.</p>
            </div>
            <button type="button" class="btn btn-primary btn-sm rounded f-12" data-logs-toggle>
                <i class="fa fa-plus mr-1" aria-hidden="true"></i>
                <span>Expand detailed log</span>
            </button>
        </div>
    </div>
    <div class="d-none p-20" data-logs-panel>
        <div class="monitor-search-panel table-responsive">
            @include('monitor::partials.table-search', [
                'id' => 'monitor-apps-log-search',
                'placeholder' => __('monitor::app.searchAppOrProcess'),
            ])
            <table class="table table-hover w-100 monitor-searchable-table">
                <thead>
                    <tr>
                        <th>Application</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Duration</th>
                        <th>Category</th>
                        <th>Productivity</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $index => $row)
                        @php
                            $searchHaystack = strtolower(implode(' ', array_filter([
                                $row['app_name'] ?? '',
                                $row['process_name'] ?? '',
                                $row['window_title'] ?? '',
                                $row['url'] ?? '',
                                $row['category_label'] ?? '',
                                $row['status_label'] ?? '',
                            ])));
                            $detailsId = 'app-log-details-' . $index;
                            $badgeClass = match ($row['category'] ?? null) {
                                'productive' => 'badge-success',
                                'unproductive' => 'badge-danger',
                                default => 'badge-secondary',
                            };
                        @endphp
                        <tr class="monitor-search-row" data-search="{{ $searchHaystack }}">
                            <td>
                                <div class="d-flex align-items-center">
                                    @include('monitor::analytics.partials.app-icon', [
                                        'size' => 28,
                                        'iconUrl' => $row['icon_url'] ?? null,
                                        'letterAvatar' => $row['letter_avatar'] ?? null,
                                        'alt' => $row['app_name'] ?? '',
                                    ])
                                    <div class="ml-2">
                                        <p class="text-truncate f-w-500 text-darkest-grey mb-0">{{ $row['app_name'] ?? 'Unknown' }}</p>
                                        <p class="text-truncate f-12 text-lightest mb-0">{{ $row['process_name'] ?? '—' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-nowrap f-14 text-dark-grey">{{ $row['started_at'] ?? '—' }}</td>
                            <td class="text-nowrap f-14 text-dark-grey">{{ $row['ended_at'] ?? '—' }}</td>
                            <td class="text-nowrap f-w-500 text-darkest-grey">{{ $row['duration_label'] ?? '0m' }}</td>
                            <td><span class="badge {{ $badgeClass }}">{{ $row['category_label'] ?? '—' }}</span></td>
                            <td class="f-14 text-dark-grey">{{ $row['productivity_label'] ?? '—' }}</td>
                            <td><span class="badge badge-secondary">{{ $row['status_label'] ?? 'Completed' }}</span></td>
                            <td class="text-right">
                                <button type="button"
                                    class="btn btn-secondary btn-sm rounded f-12"
                                    data-log-toggle="#{{ $detailsId }}"
                                    aria-expanded="false">
                                    <i class="fa fa-plus mr-1" aria-hidden="true"></i>
                                    View Details
                                </button>
                            </td>
                        </tr>
                        <tr id="{{ $detailsId }}" class="d-none bg-additional-grey">
                            <td colspan="8" class="p-20">
                                <div class="row bg-white border-grey rounded p-20">
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <div class="f-11 text-lightest text-uppercase">Window Title</div>
                                        <div class="f-14 text-darkest-grey mt-1">{{ $row['window_title'] ?? '—' }}</div>
                                    </div>
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <div class="f-11 text-lightest text-uppercase">URL</div>
                                        <div class="f-14 text-darkest-grey mt-1 text-break">{{ $row['url'] ?? '—' }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="f-11 text-lightest text-uppercase">Trend vs Average</div>
                                        <div class="f-14 mt-1 {{ $row['trend_vs_average_tone'] === 'green' ? 'text-success' : ($row['trend_vs_average_tone'] === 'amber' ? 'text-warning' : 'text-dark-grey') }}">
                                            {{ $row['trend_vs_average_label'] ?? 'Within normal range' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center f-14 text-lightest p-20">No detailed application logs available.</td>
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
