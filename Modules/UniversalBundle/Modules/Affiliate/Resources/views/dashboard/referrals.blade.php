<x-cards.data :title="__('affiliate::app.topAffiliates')" padding="false" otherClasses="h-200">
    <div class="table-responsive mt-2">
        <x-table class="border-0 mb-0 admin-dash-table table-hover">

            <x-slot name="thead">
                <th class="pl-20">#</th>
                <th>@lang('affiliate::app.affiliateName')</th>
                <th>@lang('affiliate::app.totalReferrals')</th>
                <th>@lang('app.createdAt')</th>
            </x-slot>

            @forelse($topAffiliates as $key => $item)
                <tr id="row-{{ $item->id }}">
                    <td class="pl-20">{{ $key + 1 }}</td>
                    <td>
                        {{ $item->user ? $item->user->name : '--' }}
                    </td>
                    <td>
                        {{ $item->total_referrals ? $item->total_referrals : 0 }}
                    </td>
                    <td>
                        {{ $item->created_at ? $item->created_at->timezone(global_setting()->timezone)->translatedFormat(global_setting()->date_format . ' ' . global_setting()->time_format) : '--' }}
                    </td>
                </tr>
            @empty
                <x-cards.no-record-found-list colspan="5" />
            @endforelse
        </x-table>
    </div>
</x-cards.data>
