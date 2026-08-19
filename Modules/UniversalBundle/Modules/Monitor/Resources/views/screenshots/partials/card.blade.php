<a href="javascript:;"
    class="monitor-screenshot-lightbox monitor-screenshot-card"
    data-image-url="{{ $shot['full_url'] }}"
    data-active-app="{{ $shot['active_app'] }}"
    data-window-title="{{ $shot['window_title'] }}"
    data-captured-at="{{ $shot['captured_at'] }}"
    @if (!empty($shot['task']))
        data-task-heading="{{ $shot['task']['heading'] }}"
        data-task-project="{{ $shot['task']['project_name'] ?? '' }}"
        data-task-status="{{ $shot['task']['status'] ?? '' }}"
        data-task-priority="{{ $shot['task']['priority'] ?? '' }}"
        data-task-due-date="{{ $shot['task']['due_date'] ?? '' }}"
        data-task-url="{{ $shot['task']['url'] }}"
    @endif
    title="{{ $shot['window_title'] ?? $shot['active_app'] }}">
    <div class="monitor-screenshot-thumb">
        <img src="{{ $shot['thumbnail_url'] }}" alt="{{ $shot['window_title'] ?? $shot['active_app'] }}"
            loading="lazy">
    </div>
    <div class="p-3">
        <p class="f-12 text-lightest mb-2">
            <i class="fa fa-clock mr-1" aria-hidden="true"></i>{{ $shot['captured_at'] }}
        </p>
        <div class="d-flex flex-wrap align-items-center mb-2">
            <p class="mb-0 mr-2 f-14 f-w-500 text-darkest-grey text-truncate" style="max-width: 70%;" title="{{ $shot['active_app'] }}">
                {{ $shot['active_app'] ?? '—' }}
            </p>
            @if ($shot['category'])
                <span class="badge f-11 {{ \Modules\Monitor\Services\MonitorEmployeeDetailService::categoryBadgeClass($shot['category']) }}">
                    @if ($shot['category'] === 'unproductive')
                        <span class="mr-1">⚠</span>
                    @endif
                    @if ($shot['category'] === 'productive')
                        @lang('monitor::app.categoryProductive')
                    @elseif ($shot['category'] === 'unproductive')
                        @lang('monitor::app.categoryUnproductive')
                    @else
                        @lang('monitor::app.categoryNeutral')
                    @endif
                </span>
            @endif
        </div>
        @if ($showEmployeeName && !empty($shot['employee_name']))
            <p class="mb-2 f-12 text-primary text-truncate" title="{{ $shot['employee_name'] }}">
                <i class="fa fa-user mr-1" aria-hidden="true"></i>{{ $shot['employee_name'] }}
            </p>
        @endif
        @if (!empty($shot['window_title']))
            <p class="mb-0 f-12 text-dark-grey" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" title="{{ $shot['window_title'] }}">
                {{ $shot['window_title'] }}
            </p>
        @endif
    </div>
</a>
