@if (!$html)
    <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
        <p class="mb-0 text-lightest f-14 w-30  {{ $labelClasses }}">{{ $label }}</p>
        <p class="mb-0 text-dark-grey f-14 w-70 text-wrap {{ $otherClasses }}">{!! nl2br(e($value)) !!}</p>
    </div>
@else
    <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
        <p class="mb-0 text-lightest f-14 w-30  {{ $labelClasses }}">{{ $label }}</p>
        <div class="mb-0 text-dark-grey f-14 w-70 text-wrap ql-editor p-0 {{ $otherClasses }}">{!! nl2br(clean_editor($value)) !!}</div>
    </div>
@endif
