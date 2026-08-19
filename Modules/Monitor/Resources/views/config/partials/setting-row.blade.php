@props([
    'icon' => 'cog',
    'iconClass' => 'monitor-setting-icon--grey',
    'title',
    'description' => null,
    'nested' => false,
])

<div @class([
    'config-setting-row d-flex',
    'is-nested' => $nested,
])>
    <div class="monitor-setting-icon {{ $iconClass }} mr-3">
        <i class="fa fa-{{ $icon }} f-14"></i>
    </div>
    <div class="flex-grow-1" style="min-width: 0;">
        <div class="row align-items-center">
            <div class="col-md-7 mb-3 mb-md-0">
                <p class="f-14 f-w-500 text-darkest-grey mb-0">{{ $title }}</p>
                @if ($description)
                    <p class="f-12 text-lightest mb-0 mt-1">{{ $description }}</p>
                @endif
            </div>
            <div class="col-md-5 text-md-right config-setting-control">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
