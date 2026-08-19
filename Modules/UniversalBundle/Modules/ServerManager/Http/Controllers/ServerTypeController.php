<?php

namespace Modules\ServerManager\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Modules\ServerManager\Entities\ServerType;
use Modules\ServerManager\Http\Requests\ServerType\StoreRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServerTypeController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Server Types';
    }

    public function create()
    {
        $this->serverTypes = ServerType::where('company_id', company()->id)->get();
        return view('servermanager::hosting.server-type.manage', $this->data);
    }

    public function store(StoreRequest $request)
    {
        abort_403(user()->permission('manage_servertype') !== 'all');

        $serverType = new ServerType();
        $serverType->company_id = company()->id;
        $serverType->name = trim($request->name);
        $serverType->slug = Str::slug($request->name);
        $serverType->description = $request->description ? trim_editor($request->description) : null;
        $serverType->status = $request->status ?? 'active';
        $serverType->created_by = user()->id;
        $serverType->save();

        $allServerTypes = ServerType::where('company_id', company()->id)
            ->where('status', 'active')
            ->get();

        $options = '<option value="">--</option>';

        foreach ($allServerTypes as $type) {
            $options .= '<option value="' . $type->id . '">' . $type->name . '</option>';
        }

        return Reply::successWithData(__('messages.recordSaved'), ['data' => $options]);
    }

    public function update(Request $request, $id)
    {
        abort_403(user()->permission('manage_servertype') !== 'all');

        $serverType = ServerType::where('company_id', company()->id)->findOrFail($id);

        if($request->name != null){
            $serverType->name = trim($request->name);
            $serverType->slug = Str::slug($request->name);
        }

        if($request->description != null){
            $serverType->description = trim_editor($request->description);
        }

        if ($request->has('status')) {
            $serverType->status = $request->status;
        }

        $serverType->updated_by = user()->id;
        $serverType->save();

        $allServerTypes = ServerType::where('company_id', company()->id)->where('status', 'active')->get();

        $options = '<option value="">--</option>';

        foreach ($allServerTypes as $type) {
            $options .= '<option value="' . $type->id . '">' . $type->name . '</option>';
        }

        return Reply::successWithData(__('messages.recordSaved'), ['data' => $options]);
    }

    public function destroy($id)
    {
        abort_403(user()->permission('manage_servertype') !== 'all');

        $serverType = ServerType::where('company_id', company()->id)->findOrFail($id);

        // Check if server type is being used
        if ($serverType->hostings()->count() > 0) {
            return Reply::error(__('Cannot delete server type that is being used by hostings'));
        }

        $serverType->delete();

        $allServerTypes = ServerType::where('company_id', company()->id)
            ->where('status', 'active')
            ->get();

        $options = '<option value="">--</option>';

        foreach ($allServerTypes as $type) {
            $options .= '<option value="' . $type->id . '">' . $type->name . '</option>';
        }

        return Reply::successWithData(__('messages.recordSaved'), ['data' => $options]);
    }
}
