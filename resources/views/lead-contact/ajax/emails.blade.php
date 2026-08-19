<div class="table-responsive p-20">
    @if (isset($emailHistories) && count($emailHistories) > 0)
        <table class="table table-hover mb-0">
            <thead>
                <tr class="text-dark-grey f-12">
                    <th>@lang('app.date')</th>
                    <th>@lang('modules.deal.dealName')</th>
                    <th>@lang('app.subject')</th>
                    <th>@lang('modules.deal.templateUsed')</th>
                    <th>@lang('app.email')</th>
                    <th>@lang('app.by')</th>
                    <th class="text-right">@lang('app.action')</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($emailHistories as $history)
                    <tr class="f-13">
                        <td>
                            {{ $history->created_at->timezone(company()->timezone)->translatedFormat(company()->date_format . ' ' . company()->time_format) }}
                        </td>
                        <td>
                            @if ($history->deal)
                                <a href="{{ route('deals.show', $history->deal_id) }}" class="text-darkest-grey">
                                    {{ $history->deal->name }}
                                </a>
                            @else
                                --
                            @endif
                        </td>
                        <td>{{ $history->subject }}</td>
                        <td>{{ $history->template?->name ?? '--' }}</td>
                        <td>
                            {{ $history->recipient_name ?: '--' }}
                            @if ($history->recipient_email)
                                <br><span class="text-dark-grey f-12">{{ $history->recipient_email }}</span>
                            @endif
                        </td>
                        <td>{{ $history->sentBy?->name ?? '--' }}</td>
                        <td class="text-right">
                            <a class="openRightModal text-dark-grey" href="{{ route('deal-emails.show', $history->id) }}">
                                @lang('modules.deal.viewEmail')
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <x-cards.no-record icon="envelope" :message="__('messages.noRecordFound')" />
    @endif
</div>
