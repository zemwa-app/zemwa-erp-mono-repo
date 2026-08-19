<x-cards.data :title="__('affiliate::app.latestReferredCompanies')" padding="false" otherClasses="h-200">
    <div class="table-responsive mt-2">

        <x-table class="border-0 mb-0 admin-dash-table table-hover">
            <x-slot name="thead">
                <th class="pl-20">#</th>
                <th>@lang('app.company_name')</th>
                <th class="text-center">@lang('affiliate::app.ip')</th>
                <th class="text-center">@lang('affiliate::app.userDevice')</th>
                <th class="text-center">@lang('affiliate::app.menu.affiliate')</th>
                <th class="text-center pr-20">@lang('affiliate::app.referred')</th>
            </x-slot>

            @forelse($latestCompanies as $key => $item)
                <tr id="row-{{ $item->id }}">
                    <td class="pl-20">{{ $key + 1 }}</td>
                    <td>
                        @if ($item->company) <x-company :company="$item->company" />@else '--' @endif
                    </td>
                    <td class="text-center">
                        {{ $item->ip ? $item->ip : '--' }}
                    </td>
                    <td class="text-center">
                        {{ $item->user_agent ? $item->user_agent : '--' }}
                    </td>
                    <td class="text-center">
                        {{ ($item->affiliate && $item->affiliate->user) ? $item->affiliate->user->name : '--' }}
                    </td>
                    <td class="text-center pr-20">
                        {{ $item->company ? $item->created_at->timezone(global_setting()->timezone)->translatedFormat(global_setting()->date_format . ' ' . global_setting()->time_format) : '--' }}
                    </td>
                </tr>
            @empty
                <x-cards.no-record-found-list colspan="7"/>
            @endforelse
        </x-table>

    </div>
</x-cards.data>
