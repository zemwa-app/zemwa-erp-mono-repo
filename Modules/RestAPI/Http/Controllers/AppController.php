<?php

namespace Modules\RestAPI\Http\Controllers;

use App\Models\Company;
use App\Models\GlobalSetting;
use Froiden\RestAPI\ApiResponse;
use Froiden\RestAPI\Exceptions\ApiException;
use Illuminate\Routing\Controller;


class AppController extends Controller
{

    /**
     * @throws ApiException
     */
    public function app()
    {
        $setting = GlobalSetting::select(['global_app_name', 'logo','light_logo'])->first();

        if (!module_enabled('Subdomain')) {
            $setting->company_name = $setting->global_app_name;

            return ApiResponse::make('Application data fetched successfully', $setting->toArray());
        }

        $company = Company::where('sub_domain', request()->getHost())
            ->select([
                'id',
                'sub_domain',
                'show_review_modal',
                'currency_id',
                'company_phone',
                'rounded_theme',
                'google_map_key',
                'datatable_row_limit',
                'stripe_id',
                'currency_key_version',
                'employee_can_export_data',
                'card_brand',
                'card_last_four',
                'headers',
                'ticket_form_google_captcha',
                'show_new_webhook_alert',
                'pm_last_four',
                'pm_type',
                'location_details',
                'lead_form_google_captcha'
            ])->first();

        if (!$company) {
            $exception = new ApiException('Please enter correct subdomain url your company', null, 403, 403, 2026);

            return ApiResponse::exception($exception);
        }

        if ($company->status == 'inactive') {
            return ApiResponse::exception(new ApiException('The company is currently inactive.', null, 403, 403, 2015));
        }

        if ($company->status == 'license_expired') {
            return ApiResponse::exception(new ApiException('The Company license is expired', null, 403, 403, 2015));
        }

        return ApiResponse::make('Company data fetched successfully', $company->toArray());
    }

}
