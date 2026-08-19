@php
    $partnerOrderUrl = 'https://envato.froid.works/my-account';
@endphp

<div class="row border-top-grey pt-3 mt-3">
    <div class="col-sm-12">
        <div class="border rounded overflow-hidden bg-white">
            <div class="px-3 py-2 border-bottom-grey" style="background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);">
                <div class="d-flex align-items-center">
                    <img src="https://envato.froid.works/logo-froiden.png" alt="Froiden" class="mr-2 flex-shrink-0" style="height: 28px;">
                    <div class="min-w-0">
                        <div class="d-flex align-items-center flex-wrap">
                            <h4 class="f-16 text-dark font-weight-bold mb-0 mr-2">@lang('modules.update.partnerProductsTitle')</h4>
                            <span class="badge badge-primary f-10">@lang('modules.update.partnerProductsBadge')</span>
                        </div>
                        <p class="text-lightest f-12 mb-0 mt-1">@lang('modules.update.partnerProductsBody')</p>
                    </div>
                </div>
            </div>

            <div class="p-3">
                <div class="row">
                    {{-- Mobile App --}}
                    <div class="col-lg-6 col-md-12 mb-3 mb-lg-0">
                        <div class="card border h-100 mb-0 partner-product-card partner-product-card--mobile">
                            <div class="card-body d-flex flex-column p-3">
                                <div class="d-flex align-items-start mb-2">
                                    <div class="partner-product-icon partner-product-icon--mobile mr-2 flex-shrink-0">
                                        <i class="fa fa-mobile" aria-hidden="true"></i>
                                    </div>
                                    <div class="min-w-0 flex-grow-1">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div>
                                                <h5 class="f-14 text-dark font-weight-bold mb-0">@lang('modules.update.mobileAppOrderTitle')</h5>
                                                <p class="text-lightest f-11 mb-0">@lang('modules.update.mobileAppOrderSubtitle')</p>
                                            </div>
                                            <span class="badge badge-primary f-10 ml-2 flex-shrink-0">@lang('modules.update.mobileAppOrderBadge')</span>
                                        </div>
                                    </div>
                                </div>

                                <ul class="list-unstyled mb-2 flex-grow-1">
                                    @foreach (trans('modules.update.mobileAppOrderFeatures') as $feature)
                                        <li class="d-flex align-items-start mb-1">
                                            <i class="fa fa-check text-primary mr-1 mt-1 f-10" aria-hidden="true"></i>
                                            <span class="text-dark-grey f-12">{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                </ul>

                                <a href="{{ $partnerOrderUrl }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="btn btn-primary btn-sm rounded f-12 d-inline-flex align-items-center justify-content-center w-100">
                                    <i class="fa fa-shopping-cart mr-1" aria-hidden="true"></i>
                                    @lang('modules.update.placeOrder')
                                    <i class="fa fa-external-link-alt ml-1 f-10" aria-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Employee Monitoring App --}}
                    <div class="col-lg-6 col-md-12">
                        <div class="card border h-100 mb-0 partner-product-card partner-product-card--monitoring">
                            <div class="card-body d-flex flex-column p-3">
                                <div class="d-flex align-items-start mb-2">
                                    <div class="partner-product-icon partner-product-icon--monitoring mr-2 flex-shrink-0">
                                        <i class="fa fa-desktop" aria-hidden="true"></i>
                                    </div>
                                    <div class="min-w-0 flex-grow-1">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div>
                                                <h5 class="f-14 text-dark font-weight-bold mb-0">@lang('modules.update.employeeMonitoringOrderTitle')</h5>
                                                <p class="text-lightest f-11 mb-0">@lang('modules.update.employeeMonitoringOrderSubtitle')</p>
                                            </div>
                                            <span class="badge badge-success f-10 ml-2 flex-shrink-0">@lang('modules.update.employeeMonitoringOrderBadge')</span>
                                        </div>
                                    </div>
                                </div>

                                <ul class="list-unstyled mb-2 flex-grow-1">
                                    @foreach (trans('modules.update.employeeMonitoringOrderFeatures') as $feature)
                                        <li class="d-flex align-items-start mb-1">
                                            <i class="fa fa-check text-success mr-1 mt-1 f-10" aria-hidden="true"></i>
                                            <span class="text-dark-grey f-12">{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                </ul>

                                <a href="{{ $partnerOrderUrl }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="btn btn-success btn-sm rounded f-12 d-inline-flex align-items-center justify-content-center w-100">
                                    <i class="fa fa-shopping-cart mr-1" aria-hidden="true"></i>
                                    @lang('modules.update.placeOrder')
                                    <i class="fa fa-external-link-alt ml-1 f-10" aria-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-lightest f-11 mb-0 mt-2 text-center">
                    <i class="fa fa-lock mr-1" aria-hidden="true"></i>
                    @lang('modules.update.partnerProductsFooter')
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    .partner-product-card--mobile {
        background: linear-gradient(180deg, #f8faff 0%, #ffffff 50%);
        border-color: #dbeafe !important;
    }

    .partner-product-card--monitoring {
        background: linear-gradient(180deg, #f4fbf7 0%, #ffffff 50%);
        border-color: #bbf7d0 !important;
    }

    .partner-product-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        border: 1px solid transparent;
    }

    .partner-product-icon--mobile {
        background: #eef4ff;
        color: #2563eb;
        border-color: #dbeafe;
    }

    .partner-product-icon--monitoring {
        background: #ecfdf3;
        color: #059669;
        border-color: #bbf7d0;
    }
</style>
