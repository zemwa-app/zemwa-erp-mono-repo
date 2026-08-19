<div class="card-header bg-white border-bottom-grey p-20">
    <h4 class="f-16 f-w-500 mb-0">@lang('monitor::app.websiteUsage')</h4>
</div>
<div class="card-body p-20">
    <div class="table-responsive">
        <table class="table table-hover w-100 f-14 mb-0">
            <thead>
                <tr class="text-uppercase f-11 text-lightest">
                    <th style="width: 40px;"></th>
                    <th>@lang('app.employee')</th>
                    <th>@lang('monitor::app.domain')</th>
                    <th>@lang('app.category')</th>
                    <th class="text-right">@lang('app.duration')</th>
                    <th class="text-right">@lang('monitor::app.pctOfTotal')</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($report['rows'] as $row)
                    <tr>
                        <td>
                            @include('monitor::analytics.partials.app-icon', [
                                'size' => 28,
                                'iconUrl' => $row['icon_url'] ?? null,
                                'letterAvatar' => $row['letter_avatar'] ?? null,
                                'alt' => $row['app_name'],
                            ])
                        </td>
                        <td class="f-w-500 text-darkest-grey">{{ $row['employee'] }}</td>
                        <td class="text-dark-grey">{{ $row['app_name'] }}</td>
                        <td>
                            <span class="badge {{ \Modules\Monitor\Services\Analytics\MonitorAnalyticsHelper::productivityCategoryBadgeClass($row['category']) }}">
                                {{ \Modules\Monitor\Services\Analytics\MonitorAnalyticsHelper::productivityCategoryLabel($row['category']) }}
                            </span>
                        </td>
                        <td class="text-right text-dark-grey">{{ $row['duration_label'] }}</td>
                        <td class="text-right f-w-500 text-darkest-grey">{{ $row['pct'] }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 f-14 text-lightest">@lang('messages.noRecordFound')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
