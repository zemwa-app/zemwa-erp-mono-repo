<?php

namespace Modules\ProBundle\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Modules\ProBundle\Entities\ProBundleSetting;
use Modules\ProBundle\Entities\ProModuleInstall;
use \Nwidart\Modules\Facades\Module;

class ProBundleController extends AccountBaseController
{

    public function installProBundleModule(Request $request)
    {
        $modulePath = getProBundleModulesPath() . '/' . $request->module;

        if (!file_exists($modulePath)) {
            return Reply::error(__('probundle::app.moduleIsNotAvailable', ['module' => $request->module]));
        }

        $proBundleSetting = ProBundleSetting::first();
        if (!$proBundleSetting) {
            $proBundleSetting = ProBundleSetting::create(['purchase_code' => 'my-custom-code-123']);
        }

        if (!$proBundleSetting->purchase_code) {
            $proBundleSetting->purchase_code = 'my-custom-code-123';
            $proBundleSetting->save();
        }

        $moduleInstallationPath = base_path() . '/Modules/' . $request->module;

        if (File::exists($moduleInstallationPath)) {
            File::deleteDirectory($moduleInstallationPath);
        }

        File::copyDirectory($modulePath, $moduleInstallationPath);

        cache()->forget('laravel-modules');

        $appModule = Module::findOrFail($request->module);
        $appModule->enable();

        Artisan::call('module:migrate', array($request->module, '--force' => true));

        return Reply::success(__('probundle::app.moduleIsInstalling', ['module' => $request->module]));
    }

    public function addProModulePurchaseCode(Request $request)
    {
        $proBundleSetting = ProBundleSetting::first();
        if (!$proBundleSetting) {
            $proBundleSetting = ProBundleSetting::create(['purchase_code' => 'my-custom-code-123']);
        }

        if (!$proBundleSetting->purchase_code) {
            $proBundleSetting->purchase_code = 'my-custom-code-123';
            $proBundleSetting->save();
        }

        $appModule = Module::findOrFail($request->module);

        ProModuleInstall::updateOrCreate([
            'module_name' => $request->module,
        ], [
            'version' => File::get($appModule->getPath() . '/version.txt'),
        ]);

        if (config(strtolower($request->module) . '.setting')) {
            $fetchSetting = config(strtolower($request->module) . '.setting')::first();
            if ($fetchSetting && !$fetchSetting->purchase_code) {
                $fetchSetting->purchase_code = $proBundleSetting->purchase_code;
                $fetchSetting->save();
            }
        }

        $user = auth()->id();
        cache()->flush();
        session()->flush();
        auth()->loginUsingId($user);

        return Reply::success(__('probundle::app.moduleIsInstalled', ['module' => $request->module]));
    }

}
