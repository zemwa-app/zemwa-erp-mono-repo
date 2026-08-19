@php
    use Modules\Monitor\Services\Analytics\PeriodHelper;
    $currentPeriod = $period ?? PeriodHelper::DEFAULT_TEAM;
    $preserve = $preserveQuery ?? [];
@endphp
<form method="GET" action="{{ $action }}" class="d-flex align-items-center">
    @foreach ($preserve as $key => $value)
        @if ($value !== null && $value !== '')
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach
    <div class="select-box d-flex">
        <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.duration')</p>
        <div class="select-status">
            <select name="period" id="analytics-period" class="form-control select-picker" data-size="8" data-container="body" onchange="this.form.submit()">
                @foreach (PeriodHelper::options() as $key => $label)
                    <option value="{{ $key }}" @selected($currentPeriod === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
</form>
