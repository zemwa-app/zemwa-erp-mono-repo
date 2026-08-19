<?php

namespace Modules\Monitor\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Monitor\Entities\ProductivityRule;
use Modules\Monitor\Services\Analytics\ProductivityClassifierService;
use Modules\Monitor\Services\MonitorPermissionScope;
use Illuminate\Support\Facades\Artisan;

class MonitorProductivityRulesController extends AccountBaseController
{
    public function __construct(
        private readonly ProductivityClassifierService $classifier,
        private readonly MonitorPermissionScope $permissionScope,
    ) {
        parent::__construct();
        $this->pageTitle = 'monitor::app.categoryRulesTitle';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('monitor', $this->user->modules));

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $this->permissionScope->authorizeView();

        $companyId = company()->id;
        $tab = $request->query('tab', 'all');

        $rulesQuery = ProductivityRule::query()
            ->where(function ($q) use ($companyId) {
                $q->whereNull('company_id')->orWhere('company_id', $companyId);
            });

        if ($request->filled('category') && in_array($request->category, ['productive', 'neutral', 'unproductive'], true)) {
            $rulesQuery->where('category', $request->category);
        }

        if ($request->filled('type') && in_array($request->type, ['url', 'app'], true)) {
            $rulesQuery->where('type', $request->type);
        }

        if ($tab === 'overrides') {
            $rulesQuery->where('company_id', $companyId);
        } elseif ($tab === 'global') {
            $rulesQuery->whereNull('company_id');
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $rulesQuery->where('pattern', 'like', $search);
        }

        $this->rules = $rulesQuery
            ->orderByRaw('CASE WHEN company_id IS NOT NULL THEN 1 ELSE 0 END DESC')
            ->orderByDesc('priority')
            ->orderBy('pattern')
            ->paginate(50)
            ->withQueryString();

        $this->activeTab = $tab;
        $this->filters = $request->only(['category', 'type', 'search']);
        $this->subcategories = ProductivityRule::subcategoriesByCategory();
        $this->uncategorised = $tab === 'uncategorised'
            ? $this->classifier->getUncategorisedSummary($companyId)
            : [];

        return view('monitor::productivity-rules.index', $this->data);
    }

    public function store(Request $request)
    {
        abort_403(!in_array(user()->permission('view_monitor'), ['all', 'added']));

        $data = $this->validated($request);
        $data['company_id'] = company()->id;
        $data['priority'] = ProductivityClassifierService::ORG_PRIORITY;

        ProductivityRule::create($data);
        $this->dispatchReclassify(company()->id);

        return Reply::success(__('monitor::app.ruleSaved'));
    }

    public function update(Request $request, int $id)
    {
        abort_403(!in_array(user()->permission('view_monitor'), ['all', 'added']));

        $rule = ProductivityRule::where('company_id', company()->id)->findOrFail($id);
        $rule->update($this->validated($request, $id));
        $this->dispatchReclassify(company()->id);

        return Reply::success(__('monitor::app.ruleSaved'));
    }

    public function destroy(int $id)
    {
        abort_403(!in_array(user()->permission('view_monitor'), ['all', 'added']));

        $rule = ProductivityRule::where('company_id', company()->id)->findOrFail($id);
        $rule->delete();
        $this->dispatchReclassify(company()->id);

        return Reply::success(__('monitor::app.ruleDeleted'));
    }

    public function reclassify()
    {
        abort_403(!in_array(user()->permission('view_monitor'), ['all', 'added']));

        $this->dispatchReclassify(company()->id);

        return Reply::success(__('monitor::app.reclassifyQueued'));
    }

    private function validated(Request $request, ?int $ignoreRuleId = null): array
    {
        $subcategories = ProductivityRule::subcategoriesByCategory();

        $uniquePattern = Rule::unique('productivity_rules', 'pattern')
            ->where(fn ($q) => $q->where('company_id', company()->id)->where('type', $request->input('type')));

        if ($ignoreRuleId) {
            $uniquePattern->ignore($ignoreRuleId);
        }

        $validated = $request->validate([
            'type' => 'required|in:url,app',
            'pattern' => ['required', 'string', 'max:255', $uniquePattern],
            'category' => 'required|in:productive,neutral,unproductive',
            'subcategory' => [
                'required',
                'string',
                Rule::in($subcategories[$request->input('category')] ?? []),
            ],
        ]);

        $validated['pattern'] = strtolower(trim($validated['pattern']));

        return $validated;
    }

    private function dispatchReclassify(int $companyId): void
    {
        ProductivityClassifierService::markRulesChanged($companyId);
        Artisan::queue('monitor:classify-activity-logs', [
            '--company' => $companyId,
            '--days' => 30,
        ]);
    }
}
