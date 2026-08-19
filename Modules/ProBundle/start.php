<?php

use Illuminate\Support\Facades\File;
use Modules\ProBundle\Entities\ProModuleInstall;

if (! function_exists('isInstallFromProBundleModule')) {

    function isInstallFromProBundleModule($name)
    {
        return ProModuleInstall::where('module_name', $name)->exists();
    }

}

if (! function_exists('getProBundleModules')) {

    function getProBundleModules()
    {
        return ProModuleInstall::all();
    }

}

if (! function_exists('getProBundleModule')) {

    function getProBundleModule($name)
    {
        return ProModuleInstall::where('module_name', $name)->first();
    }

}

if (! function_exists('getProBundleModulesPath')) {

    function getProBundleModulesPath()
    {
        return module_path('ProBundle', 'Modules');
    }

}

if (! function_exists('getProBundleAvailableModules')) {

    function getProBundleAvailableModules()
    {
        $modulesPath = getProBundleModulesPath();
        $modules = [];

        if (file_exists($modulesPath)) {
            // get only directories
            $modules = File::directories($modulesPath);
            // remove path from array
            $modules = array_map(function ($module) {
                return basename($module);
            }, $modules);
        }

        return $modules;
    }

}

if (! function_exists('getProBundleAvailableForInstallModules')) {

    function getProBundleAvailableForInstallModules()
    {
        $modules = getProBundleAvailableModules();
        $installedModules = array_keys(\Nwidart\Modules\Facades\Module::all());

        $availableModules = [];

        foreach ($modules as $module) {
            if (in_array($module, $installedModules)) {
                // check version of installedModule and compare with version of bundle module
                // get version of from version.txt
                $moduleVersion = File::get(getProBundleModulesPath() . '/' .$module .'/version.txt');
                $installedModuleVersion = File::get(base_path('Modules' . '/' . $module . '/version.txt'));

                if ($moduleVersion > $installedModuleVersion) {
                    $availableModules[] = $module;
                }
            }
            else {
                $availableModules[] = $module;
            }
        }

        return $availableModules;
    }

}
