<div class="card-header bg-white border-bottom-grey p-20">
    <h4 class="f-16 f-w-500 mb-0">@lang('monitor::app.screenshotsSummary')</h4>
</div>
<div class="card-body p-20">
    <div class="table-responsive">
        <table class="table table-hover w-100 f-14 mb-0">
            <thead>
                <tr class="text-uppercase f-11 text-lightest">
                    <th>@lang('app.employee')</th>
                    <th class="text-right">@lang('monitor::app.totalScreenshots')</th>
                    <th class="text-right">@lang('monitor::app.categoryProductive')</th>
                    <th class="text-right">@lang('monitor::app.categoryNeutral')</th>
                    <th class="text-right">@lang('monitor::app.categoryUnproductive')</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($report['rows'] as $row)
                    <tr>
                        <td class="f-w-500 text-darkest-grey">{{ $row['employee'] }}</td>
                        <td class="text-right f-w-500 text-darkest-grey">{{ $row['total'] }}</td>
                        <td class="text-right text-success">{{ $row['productive'] }}</td>
                        <td class="text-right text-dark-grey">{{ $row['neutral'] }}</td>
                        <td class="text-right text-danger">{{ $row['unproductive'] }}</td>
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
