<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\GlobalSetting;
use Illuminate\Http\Request;
use App\Models\MobileAppDownloadSetting;

class DownloadSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.downloads';
        $this->activeSettingMenu = 'download_settings';

        $this->middleware(function ($request, $next) {
            abort_403(!user()->permission('manage_superadmin_app_settings'));

            return $next($request);
        });
    }

    public function index()
    {
        $this->downloadSetting = MobileAppDownloadSetting::instance();

        return view('super-admin.download-settings.index', $this->data);
    }

    public function update(Request $request)
    {
        abort_403(!user()->permission('manage_superadmin_app_settings'));

        $validated = $request->validate([
            'app_ios' => 'nullable|url',
            'app_android' => 'nullable|url',
        ]);

        $setting = MobileAppDownloadSetting::instance();
        $setting->fill($validated);
        $setting->save();

        return Reply::success(__('messages.updateSuccess'));
    }

}