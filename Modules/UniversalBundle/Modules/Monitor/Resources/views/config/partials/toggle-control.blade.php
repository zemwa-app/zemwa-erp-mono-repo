@props(['id', 'name', 'checked' => false])

<div class="custom-control custom-switch d-inline-block">
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" name="{{ $name }}" id="{{ $id }}" value="1" @checked($checked)
        class="custom-control-input" role="switch" aria-checked="{{ $checked ? 'true' : 'false' }}">
    <label class="custom-control-label cursor-pointer" for="{{ $id }}"
        title="{{ $checked ? __('app.enabled') : __('app.disabled') }}"></label>
</div>
