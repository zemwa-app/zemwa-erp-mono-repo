<x-cards.data padding="false" :title="__('monitor::app.teamLiveStatus')">
    <x-slot name="action">
        <span class="badge badge-secondary f-12">Filtered live view</span>
    </x-slot>
    <p class="f-12 text-lightest px-4 pt-3 mb-0">Live monitoring table with status, app, and productivity context.</p>
    <div class="table-responsive w-tables rounded mt-3 bg-white">
        <table class="table table-hover w-100 border-0" id="monitor-employees-table">
            <thead class="f-13 text-dark-grey">
                <tr>
                    <th>@lang('app.employee')</th>
                    <th>@lang('monitor::app.status')</th>
                    <th>@lang('monitor::app.currentApp')</th>
                    <th>@lang('monitor::app.todaysScore')</th>
                    <th>@lang('monitor::app.lastUpdated')</th>
                    <th class="text-right"></th>
                </tr>
            </thead>
            <tbody id="monitor-employees-body">
                @include('monitor::dashboard.partials.employee-rows', ['employees' => $employees])
            </tbody>
        </table>
    </div>
</x-cards.data>
