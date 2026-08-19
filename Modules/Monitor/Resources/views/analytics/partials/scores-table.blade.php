<div class="table-responsive">
    <table class="table table-hover w-100">
        <thead>
            <tr>
                <th class="f-12 text-dark-grey" style="width: 48px;">#</th>
                <th class="f-12 text-dark-grey">@lang('app.employee')</th>
                <th class="f-12 text-dark-grey" style="min-width: 140px;">@lang('monitor::app.productivity')</th>
                <th class="f-12 text-dark-grey text-right">@lang('monitor::app.activeTime')</th>
                <th class="f-12 text-dark-grey" style="width: 80px;"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td class="f-14 text-lightest">{{ $row['rank'] }}</td>
                    <td class="f-14 f-w-500">{{ $row['name'] }}</td>
                    <td class="f-14">
                        <span class="badge {{ \Modules\Monitor\Services\Analytics\MonitorAnalyticsHelper::scoreBadgeClass($row['score']) }}">{{ $row['score'] }}%</span>
                    </td>
                    <td class="f-14 text-right">{{ $row['active_hours_label'] }}</td>
                    <td class="f-14 text-right">
                        <a href="{{ $row['detail_url'] }}" class="f-12 text-primary">@lang('app.view')</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="f-14 text-lightest text-center p-20">@lang('monitor::app.noScoreData')</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
