<?php

namespace Modules\LandingPagePro\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Scopes\ModuleCompanyScope;
use DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Modules\LandingPagePro\Entities\LandingPage;
use Modules\LandingPagePro\Entities\LandingPageTemplate;
use function Psl\Str\is_empty;

class LaunchPadController extends Controller
{
	public function preview($id)
	{
		$id = (int)(Crypt::decrypt($id));
		$result = LandingPage::withoutGlobalScope(ModuleCompanyScope::class)
			->where('id', $id)
			->first();
		// If there is no previous data entered, load default content to page from template table
		if (isset($result) && ($result->template_contents == null || is_empty($result->template_contents))) {
			$defaultResult = LandingPageTemplate::withoutGlobalScope(ModuleCompanyScope::class)
				->where('id', $result->template_id)
				->first();
			$this->id = Crypt::encrypt($result->id);
			$this->content = json_decode($defaultResult->template_contents, true);

			$view = 'landingpagepro::templates.template_' . $result->template_id;
			return view($view, $this->data);
		}
		$this->content = json_decode($result->template_contents, true);
		$this->view = $view = 'landingpagepro::templates.template_' . $result->template_id;

		return view($view, $this->data);
	}

}
