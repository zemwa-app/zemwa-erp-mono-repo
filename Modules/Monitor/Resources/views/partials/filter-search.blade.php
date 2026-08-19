@props([
    'id' => 'filter-search',
    'name' => 'search',
    'value' => '',
    'placeholder' => null,
    'wrapperClass' => '',
    'showBorder' => true,
])
<div @class([
    'task-search d-flex py-1 px-lg-3 px-0 align-items-center',
    'border-right-grey' => $showBorder,
    $wrapperClass,
])>
    <div class="w-100 mr-1 mr-lg-0 mr-md-1 ml-md-1 ml-0 ml-lg-0">
        <div class="input-group bg-grey rounded">
            <div class="input-group-prepend">
                <span class="input-group-text border-0 bg-additional-grey">
                    <i class="fa fa-search f-13 text-dark-grey" aria-hidden="true"></i>
                </span>
            </div>
            <input type="text"
                name="{{ $name }}"
                id="{{ $id }}"
                value="{{ $value }}"
                placeholder="{{ $placeholder ?? __('app.search') }}"
                class="form-control f-14 p-1 border-additional-grey">
        </div>
    </div>
</div>
