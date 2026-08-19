<div class="card bg-white border-0 b-shadow-4 mb-4">
    <div class="card-header bg-white border-bottom-grey p-20">
        <div class="row align-items-center">
            <div class="col-md-8 mb-3 mb-md-0">
                <h4 class="f-16 f-w-500 mb-0">@lang('monitor::app.monitorSeatsTitle')</h4>
                <p class="f-12 text-lightest mb-0 mt-1">
                    @if ($monitorBillingEnabled ?? false)
                        @lang('monitor::app.monitorSeatsHelp')
                    @else
                        @lang('monitor::app.monitorSeatsHelpNonBilling')
                    @endif
                </p>
            </div>
            <div class="col-md-4 text-md-right">
                <div class="d-inline-flex align-items-center f-12 f-w-500 text-primary bg-additional-grey rounded px-3 py-2">
                    <span id="enabled-monitor-seat-count">{{ $enabledMonitorSeatCount }}</span>
                    <span class="ml-1">@lang('monitor::app.monitorSeatsEnabled')</span>
                    @if (($monitorBillingEnabled ?? false) && $monitorPerSeatPrice)
                        <span class="text-lightest mx-2">·</span>
                        <span>{{ global_currency_format($monitorPerSeatPrice, company()->package->currency_id) }}/@lang('app.month')</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card-body monitor-search-panel p-20">
        @include('monitor::partials.table-search', [
            'id' => 'monitor-seats-search',
            'placeholder' => __('monitor::app.searchEmployee'),
        ])
        <div class="table-responsive">
            <table class="monitor-seats-table monitor-searchable-table table table-hover w-100 f-14 mb-0">
                <thead>
                    <tr class="text-uppercase f-11 text-lightest">
                        <th>@lang('app.employee')</th>
                        <th>@lang('app.email')</th>
                        <th class="text-right">@lang('monitor::app.employeeMonitoring')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($monitorSeats as $seat)
                        <tr class="monitor-search-row"
                            data-user-id="{{ $seat['id'] }}"
                            data-search="{{ strtolower($seat['name'] . ' ' . $seat['email']) }}">
                            <td class="f-w-500 text-darkest-grey">{{ $seat['name'] }}</td>
                            <td class="text-dark-grey">{{ $seat['email'] }}</td>
                            <td class="text-right">
                                @include('monitor::config.partials.toggle-control', [
                                    'id' => 'monitor-seat-' . $seat['id'],
                                    'name' => 'monitoring_enabled',
                                    'checked' => $seat['monitoring_enabled'],
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 f-14 text-lightest">
                                @lang('messages.noRecordFound')
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function () {
            $('.monitor-seats-table').on('change', 'input[name="monitoring_enabled"]', function () {
                const $row = $(this).closest('tr');
                const userId = $row.data('user-id');
                const enabled = $(this).is(':checked');

                $.easyAjax({
                    url: "{{ route('monitor.seats.toggle', ['userId' => '__USER__']) }}".replace('__USER__', userId),
                    type: 'POST',
                    blockUI: true,
                    data: {
                        _token: '{{ csrf_token() }}',
                        monitoring_enabled: enabled ? 1 : 0,
                    },
                    success: function (response) {
                        if (response.enabledMonitorSeatCount !== undefined) {
                            $('#enabled-monitor-seat-count').text(response.enabledMonitorSeatCount);
                        }
                    },
                    error: function () {
                        $(this).prop('checked', !enabled);
                    }.bind(this),
                });
            });
        });
    </script>
@endpush
