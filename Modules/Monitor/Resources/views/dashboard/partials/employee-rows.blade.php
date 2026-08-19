@forelse ($employees as $employee)
    @include('monitor::dashboard.partials.employee-row', ['employee' => $employee])
@empty
    <tr>
        <td colspan="6" class="text-center f-14 text-lightest p-4">@lang('messages.noRecordFound')</td>
    </tr>
@endforelse
