@extends('layouts.app')

@push('styles')
@endpush

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="px-4 py-0 py-lg-4 border-top-0 super-admin-dashboard">

        <div class="row">
            <div class="col-md-3 mb-4">
                <x-cards.widget :title="__('affiliate::app.totalAffiliates')" :value="$totalAffiliates" icon="user" />
            </div>
            <div class="col-md-3 mb-4">
                <x-cards.widget :title="__('affiliate::app.totalReferrals')" :value="$totalReferrals" icon="list-alt" />
            </div>
            <div class="col-md-3 mb-4">
                <x-cards.widget :title="__('affiliate::app.totalPayouts')" :value="$totalPayouts" icon="coins" />
            </div>
            <div class="col-md-3 mb-4">
                <x-cards.widget :title="__('affiliate::app.pendingPayouts')" :value="$pendingPayouts" icon="clock" />
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12 col-lg-6 mt-4">
                @include('affiliate::dashboard.referrals')
            </div>
            <div class="col-sm-12 col-lg-6 mt-4">
                @include('affiliate::dashboard.companies')
            </div>
        </div>
    </div>
    <!-- CONTENT WRAPPER END -->
@endsection

@push('scripts')
@endpush
