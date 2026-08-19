<div class="p-20">
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('monitor.analytics.export', array_merge(request()->query(), ['tab' => 'projects'])) }}"
            class="btn btn-secondary btn-xs">
            <i class="fa fa-download text-success mr-1" aria-hidden="true"></i>
            @lang('monitor::app.exportCsv')
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover w-100">
            <thead>
                <tr>
                    <th class="f-12 text-dark-grey">@lang('app.project')</th>
                    <th class="f-12 text-dark-grey text-right">@lang('monitor::app.loggedHours')</th>
                    <th class="f-12 text-dark-grey text-right">@lang('monitor::app.hoursAllocated')</th>
                    <th class="f-12 text-dark-grey">@lang('monitor::app.budgetStatus')</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td class="f-14 f-w-500 text-darkest-grey">{{ $row['project_name'] }}</td>
                        <td class="f-14 text-right">{{ number_format($row['logged_hours'], 2) }}h</td>
                        <td class="f-14 text-right">{{ $row['budget_hours'] !== null ? number_format($row['budget_hours'], 1) . 'h' : '—' }}</td>
                        <td class="f-14">
                            <span class="f-12 f-w-500 {{ $row['status_class'] }}">
                                @if ($row['status_icon'])
                                    <i class="fa fa-{{ $row['status_icon'] }} mr-1"></i>
                                @else
                                    <span class="d-inline-block rounded-circle mr-1 {{ $row['status'] === 'on_track' ? 'bg-green-500' : ($row['status'] === 'near' ? 'bg-yellow-500' : 'bg-gray-200') }}" style="width: 8px; height: 8px;"></span>
                                @endif
                                {{ $row['status_label'] }}
                                @if ($row['budget_pct'] !== null)
                                    ({{ $row['budget_pct'] }}%)
                                @endif
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="f-14 text-lightest text-center p-20">@lang('messages.noRecordFound')</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
