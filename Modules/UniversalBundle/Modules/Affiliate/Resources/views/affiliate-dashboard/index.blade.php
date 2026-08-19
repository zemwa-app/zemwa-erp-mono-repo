@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')

    <!-- FILTER START -->
    <div class="d-flex filter-box project-header bg-white">

        <div class="mobile-close-overlay w-100 h-100" id="close-client-overlay"></div>
        <div class="project-menu d-lg-flex" id="mob-client-detail">

            <a class="d-none close-it" href="javascript:;" id="close-client-detail">
                <i class="fa fa-times"></i>
            </a>

            <x-tab :href="route('affiliate-dashboard.index')"
                :text="__('affiliate::app.menu.affiliates')" class="affiliates" ajax="false"/>

            <x-tab :href="route('affiliate-dashboard.index').'?tab=payouts'" ajax="false"
                :text="__('affiliate::app.menu.payouts')" class="payouts"/>

            <x-tab :href="route('affiliate-dashboard.index').'?tab=referrals'" ajax="false"
                :text="__('affiliate::app.menu.referrals')" class="referrals"/>

        </div>

        <a class="mb-0 d-block d-lg-none text-dark-grey ml-auto mr-2 border-left-grey"
            onclick="openClientDetailSidebar()"><i class="fa fa-ellipsis-v "></i></a>

    </div>
    <!-- FILTER END -->
    <!-- PROJECT HEADER END -->

@endsection

@section('content')

    <div class="content-wrapper border-top-0 client-detail-wrapper">
        @include($view)
    </div>

@endsection

@push('scripts')
    <script>
"use strict";  // Enforces strict mode for the entire script
        $("body").on("click", ".ajax-tab", function (event) {
            event.preventDefault();

            $('.project-menu .p-sub-menu').removeClass('active');
            $(this).addClass('active');

            const requestUrl = this.href;

            $.easyAjax({
                url: requestUrl,
                blockUI: true,
                container: ".content-wrapper",
                historyPush: true,
                success: function (response) {
                    if (response.status == "success") {
                        $('.content-wrapper').html(response.html);
                        init('.content-wrapper');
                    }
                }
            });
        });

    </script>
    <script>
"use strict";  // Enforces strict mode for the entire script
        const activeTab = "{{ $activeTab }}";
        $('.project-menu .'+activeTab).addClass('active');

    </script>
@endpush

