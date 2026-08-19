<?php

namespace Modules\ServerManager\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\ServerManager\Entities\ServerDomainType;

class DomainTypeController extends AccountBaseController
{
    public function create()
    {
        abort_403(user()->permission('manage_domain_type') !== 'all');

        $this->pageTitle = __('servermanager::app.domain.domainType');

        // `$.ajaxModal` expects raw HTML (not JSON)
        return view('servermanager::domain-type.ajax.create', $this->data);
    }

    public function store(Request $request)
    {
        abort_403(user()->permission('manage_domain_type') !== 'all');

        $request->merge([
            'type' => ServerDomainType::normalizeType((string) $request->input('type')),
        ]);

        $request->validate([
            'type' => [
                'required',
                'string',
                'max:50',
                Rule::unique('server_domain_types', 'type')->where(fn ($q) => $q->where('company_id', company()->id)),
            ],
        ]);

        $type = $request->input('type');

        $domainType = ServerDomainType::create([
            'company_id' => company()->id,
            'type' => $type,
            'label' => ServerDomainType::displayLabel($type),
            'is_active' => true,
            'created_by' => user()->id,
        ]);

        $domainTypes = ServerDomainType::where('company_id', company()->id)
            ->where('is_active', true)
            ->orderBy('type')
            ->get();
        
        $optionsHtml = view('servermanager::domain-type.partials.options', [
            'domainTypes' => $domainTypes,
            'selected' => $domainType->type,
        ])->render();

        return Reply::successWithData(__('messages.recordSaved'), [
            'type' => $domainType->type,
            'optionsHtml' => $optionsHtml,
        ]);
    }
}

