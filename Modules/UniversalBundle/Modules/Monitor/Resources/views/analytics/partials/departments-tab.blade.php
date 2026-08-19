<div class="p-20">
    <div class="row mb-3">
        <div class="col-xl-3 col-lg-3 col-md-6 mb-3 mb-xl-0">
            <x-cards.widget :title="__('monitor::app.companyAverage')" :value="$company_avg . '%'" icon="building" />
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 mb-3 mb-xl-0">
            <x-cards.widget :title="__('monitor::app.onlineNow')" :value="$online_count . ' / ' . $total_employees" icon="signal" />
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 mb-3 mb-xl-0">
            <x-cards.widget :title="__('monitor::app.atRisk')" :value="(string) $at_risk_count" icon="exclamation-triangle" />
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6">
            <x-cards.widget :title="__('monitor::app.topDepartment')" :value="$top_department" icon="award" />
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover w-100">
            <thead>
                <tr>
                    <th class="f-12 text-dark-grey">@lang('app.menu.teams')</th>
                    <th class="f-12 text-dark-grey">@lang('monitor::app.productivity')</th>
                    <th class="f-12 text-dark-grey text-right">@lang('modules.dashboard.headcount')</th>
                    <th class="f-12 text-dark-grey text-right">@lang('monitor::app.loggedHours')</th>
                    <th class="f-12 text-dark-grey text-right">@lang('monitor::app.trend')</th>
                    <th style="width: 32px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr class="cursor-pointer" onclick="window.location='{{ $row['url'] }}'">
                        <td class="f-14 f-w-500 text-darkest-grey">{{ $row['name'] }}</td>
                        <td class="f-14">
                            <div class="d-flex align-items-center">
                                @include('monitor::analytics.partials.score-bar', ['score' => $row['score'], 'referenceScore' => $company_avg])
                                <span class="badge ml-2 {{ \Modules\Monitor\Services\Analytics\MonitorAnalyticsHelper::scoreBadgeClass($row['score']) }}">{{ $row['score'] }}%</span>
                            </div>
                        </td>
                        <td class="f-14 text-right">{{ $row['headcount'] }}</td>
                        <td class="f-14 text-right">{{ $row['hours'] }}</td>
                        <td class="f-12 text-right f-w-500 {{ $row['trend']['class'] }}">{{ $row['trend']['label'] }}</td>
                        <td class="f-14 text-lightest"><i class="fa fa-chevron-right"></i></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="f-14 text-lightest text-center p-20">@lang('messages.noRecordFound')</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
