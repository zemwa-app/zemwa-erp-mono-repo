<option value="" disabled selected>@lang('servermanager::app.selectDomainType')</option>
@foreach($domainTypes as $dt)
    <option value="{{ $dt->type }}" {{ $dt->type === $selected ? 'selected' : '' }}>
        {{ $dt->label ?: ('.' . $dt->type) }}
    </option>
@endforeach