<div class="modal-body p-20">
    <div class="mb-4 border-bottom-grey pb-3">
        @if (!empty($taskHeading))
            <div class="bg-additional-grey border-left-primary rounded p-3 mb-3">
                <p class="f-11 text-primary text-uppercase mb-1">@lang('app.task')</p>
                @if (!empty($taskUrl))
                    <a href="{{ $taskUrl }}" target="_blank" rel="noopener noreferrer"
                        class="f-14 f-w-500 text-primary d-flex align-items-center">
                        {{ $taskHeading }}
                        <i class="fa fa-external-link-alt ml-1 f-11" aria-hidden="true"></i>
                    </a>
                @else
                    <p class="f-14 f-w-500 text-darkest-grey mb-0">{{ $taskHeading }}</p>
                @endif
                <dl class="row mt-2 mb-0 f-12 text-dark-grey">
                    @if (!empty($taskProject))
                        <div class="col-sm-6">
                            <span class="f-w-500 text-lightest">@lang('app.project'):</span>
                            {{ $taskProject }}
                        </div>
                    @endif
                    @if (!empty($taskStatus))
                        <div class="col-sm-6">
                            <span class="f-w-500 text-lightest">@lang('app.status'):</span>
                            {{ $taskStatus }}
                        </div>
                    @endif
                    @if (!empty($taskPriority))
                        <div class="col-sm-6">
                            <span class="f-w-500 text-lightest">@lang('modules.tasks.priority'):</span>
                            {{ $taskPriority }}
                        </div>
                    @endif
                    @if (!empty($taskDueDate))
                        <div class="col-sm-6">
                            <span class="f-w-500 text-lightest">@lang('app.dueDate'):</span>
                            {{ $taskDueDate }}
                        </div>
                    @endif
                </dl>
            </div>
        @endif

        @if (!empty($activeApp))
            <p class="f-14 f-w-500 text-darkest-grey">{{ $activeApp }}</p>
        @endif
        @if (!empty($windowTitle))
            <p class="f-14 text-dark-grey mb-1">
                <span class="f-12 f-w-500 text-lightest text-uppercase d-block">@lang('monitor::app.windowTitle')</span>
                {{ $windowTitle }}
            </p>
        @endif
        @if (!empty($capturedAt))
            <p class="f-12 text-lightest mb-0">
                <i class="fa fa-clock mr-1" aria-hidden="true"></i>{{ $capturedAt }}
            </p>
        @endif
    </div>
    <img src="{{ $imageUrl }}" alt="{{ $windowTitle ?? $activeApp }}" class="w-100 rounded border-grey">
</div>
<div class="modal-footer border-top-grey">
    <x-forms.button-cancel data-dismiss="modal">@lang('app.close')</x-forms.button-cancel>
</div>
