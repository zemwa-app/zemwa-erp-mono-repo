<div class="table-responsive p-20">
    <x-table class="table-bordered">
        <x-slot name="thead">
            <th>@lang('app.name')</th>
            <th>@lang('app.subject')</th>
            <th class="text-right">@lang('app.action')</th>
        </x-slot>

        @forelse($emailTemplates as $template)
            <tr class="row{{ $template->id }}">
                <td>{{ $template->name }}</td>
                <td>{{ \Illuminate\Support\Str::limit($template->subject, 60) }}</td>
                <td class="text-right">
                    <div class="task_view">
                        <a class="task_view_more d-flex align-items-center justify-content-center edit-email-template" href="javascript:;" data-template-id="{{ $template->id }}">
                            <i class="fa fa-edit icons mr-2"></i> @lang('app.edit')
                        </a>
                    </div>
                    <div class="task_view">
                        <a class="task_view_more d-flex align-items-center justify-content-center delete-email-template" href="javascript:;" data-template-id="{{ $template->id }}">
                            <i class="fa fa-trash icons mr-2"></i> @lang('app.delete')
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3">
                    <x-cards.no-record icon="envelope" :message="__('messages.noTemplateFound')" />
                </td>
            </tr>
        @endforelse
    </x-table>
</div>

<div class="p-20 pt-0">
    <p class="f-12 text-lightest mb-1">@lang('modules.deal.mergeFields')</p>
    <p class="f-12 text-dark-grey mb-0">@lang('modules.deal.mergeFieldsHelp')</p>
</div>
