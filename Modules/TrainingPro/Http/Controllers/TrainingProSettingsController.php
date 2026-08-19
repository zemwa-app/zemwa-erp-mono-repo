<?php

namespace Modules\TrainingPro\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\TrainingPro\Entities\TrainingProSetting;

class TrainingProSettingsController extends AccountBaseController
{
	public function __construct()
	{
		parent::__construct();
		$this->pageTitle = __('trainingpro::app.menu.trainingproSetting');
		$this->activeSettingMenu = 'trainingpro_settings';

		$this->middleware(function ($request, $next) {
			abort_403(!user()->is_superadmin && !in_array(TrainingProSetting::MODULE_NAME, $this->user->modules));
			return $next($request);
		});
	}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('trainingpro::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('trainingpro::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('trainingpro::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('trainingpro::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
