<div class="card-header bg-white border-bottom-grey p-20">
    <h4 class="f-16 f-w-500 mb-0">@lang('monitor::app.idleAnalysis')</h4>
</div>
<div class="card-body p-20">
    <div class="table-responsive">
        <table class="table table-hover w-100 f-14 mb-0">
            <thead>
                <tr class="text-uppercase f-11 text-lightest">
                    <th>@lang('app.employee')</th>
                    <th>@lang('app.date')</th>
                    <th class="text-right">@lang('monitor::app.idleTime')</th>
                    <th class="text-right">@lang('monitor::app.idlePct')</th>
                    <th class="text-right">@lang('monitor::app.longestIdle')</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($report['rows'] as $row)
                    <tr>
                        <td class="f-w-500 text-darkest-grey">{{ $row['employee'] }}</td>
                        <td class="text-dark-grey">{{ $row['date'] }}</td>
                        <td class="text-right text-dark-grey">{{ $row['idle_label'] }}</td>
                        <td class="text-right">
                            <span class="badge {{ $row['idle_pct'] >= 30 ? 'badge-danger' : ($row['idle_pct'] >= 15 ? 'badge-warning' : 'badge-success') }}">
                                {{ $row['idle_pct'] }}%
                            </span>
                        </td>
                        <td class="text-right text-dark-grey">{{ $row['longest_idle'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 f-14 text-lightest">@lang('messages.noRecordFound')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
