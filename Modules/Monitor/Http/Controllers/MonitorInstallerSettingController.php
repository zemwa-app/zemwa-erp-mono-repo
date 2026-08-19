<?php

namespace Modules\Monitor\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Modules\Monitor\Services\MonitorInstallerService;

class MonitorInstallerSettingController extends AccountBaseController
{
    public function __construct(
        private readonly MonitorInstallerService $installerService,
    ) {
        parent::__construct();
        $this->pageTitle = 'monitor::app.menu.installerSettings';
        $this->activeSettingMenu = 'monitor_installer_setting';
        $this->middleware(function ($request, $next) {
            abort_403(!user()->is_superadmin);

            return $next($request);
        });
    }

    public function index()
    {
        $data = $this->installerService->getPageData(includeManageMeta: true);
        $this->version = $data['version'];
        $this->released_at = $data['released_at'];
        $this->released_at_label = $data['released_at_label'];
        $this->platforms = $data['platforms'];
        $this->can_manage = $data['can_manage'];
        $this->max_upload_mb = config('monitor.installer.max_upload_mb', 500);

        return view('monitor::installer.manage', $this->data);
    }

    public function upload(Request $request)
    {
        $this->installerService->authorizeManage();

        $this->installerService->uploadInstallers($request);

        return Reply::successWithData(__('monitor::app.installerUploadSuccess'), [
            'redirectUrl' => route('monitor.installer-settings.index'),
        ]);
    }

    public function destroy(string $platform)
    {
        $this->installerService->authorizeManage();

        $this->installerService->deleteInstaller($platform);

        return Reply::success(__('monitor::app.installerRemoved'));
    }
}
