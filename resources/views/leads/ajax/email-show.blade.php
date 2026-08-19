<div class="row">
    <div class="col-sm-12">
        <x-cards.data :title="$emailHistory->subject" class="mt-4">
            <div class="col-12 px-0 pb-3 d-flex">
                <p class="mb-0 text-lightest f-14 w-30 d-inline-block">@lang('app.to')</p>
                <p class="mb-0 text-dark-grey f-14">
                    {{ $emailHistory->recipient_name }}
                    @if ($emailHistory->recipient_email)
                        &lt;{{ $emailHistory->recipient_email }}&gt;
                    @endif
                </p>
            </div>

            <x-cards.data-row :label="__('app.date')"
                :value="$emailHistory->created_at->timezone(company()->timezone)->translatedFormat(company()->date_format . ' ' . company()->time_format)" />

            @if (!empty($emailHistory->meta['cc']))
                <x-cards.data-row :label="__('app.cc')" :value="$emailHistory->meta['cc']" />
            @endif

            @if ($emailHistory->template)
                <x-cards.data-row :label="__('modules.deal.templateUsed')" :value="$emailHistory->template->name" />
            @endif

            <x-cards.data-row :label="__('app.by')" :value="$emailHistory->sentBy?->name ?? '--'" />

            <div class="col-12 px-0 pb-3 d-flex">
                <p class="mb-0 text-lightest f-14 w-30 d-inline-block">@lang('app.status')</p>
                <p class="mb-0 text-dark-grey f-14">
                    @if ($emailHistory->status === 'sent')
                        <span class="badge badge-success">@lang('modules.deal.emailSentLabel')</span>
                    @else
                        <span class="badge badge-danger">@lang('app.failed')</span>
                        @if (!empty($emailHistory->meta['error']))
                            <span class="text-dark-grey f-12 ml-2">{{ $emailHistory->meta['error'] }}</span>
                        @endif
                    @endif
                </p>
            </div>

            <div class="col-12 px-0 pb-3">
                <p class="mb-0 text-lightest f-14 w-30 d-inline-block">@lang('app.body')</p>
                <div class="border rounded p-3 bg-white ql-editor mt-2">
                    {!! $emailHistory->body !!}
                </div>
            </div>

            @if ($emailHistory->attachments->count() > 0)
                <div class="col-12 px-0 pb-3">
                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block">@lang('modules.deal.attachmentCount')</p>
                    <div class="mt-2">
                        @foreach ($emailHistory->attachments as $attachment)
                            <a href="{{ route('deal-emails.download', [$emailHistory->id, $attachment->id]) }}"
                                class="d-inline-block mr-3 mb-2 text-dark-grey f-13">
                                <i class="fa fa-paperclip mr-1"></i>{{ $attachment->filename }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </x-cards.data>
    </div>
</div>

<script>
    $(document).ready(function() {
        init(RIGHT_MODAL);
    });
</script>
