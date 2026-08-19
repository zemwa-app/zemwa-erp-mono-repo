<?php

namespace Modules\RestAPI\Http\Controllers;

use App\Helper\Files;
use App\Models\Country;
use App\Models\EmployeeDetails;
use App\Models\Role;
use App\Scopes\ActiveScope;
use Froiden\RestAPI\ApiController;
use Froiden\RestAPI\ApiResponse;
use Modules\RestAPI\Entities\Employee;
use Modules\RestAPI\Http\Requests\Employee\CreateRequest;
use Modules\RestAPI\Http\Requests\Employee\DeleteRequest;
use Modules\RestAPI\Http\Requests\Employee\IndexRequest;
use Modules\RestAPI\Http\Requests\Employee\ShowRequest;
use Modules\RestAPI\Http\Requests\Employee\UpdateRequest;

class EmployeeController extends ApiController
{
    protected $model = Employee::class;

    protected $indexRequest = IndexRequest::class;

    protected $storeRequest = CreateRequest::class;

    protected $updateRequest = UpdateRequest::class;

    protected $showRequest = ShowRequest::class;

    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query)
    {
        return $query->visibility();
    }

    public function modifyShow($query)
    {
        return $query->withoutGlobalScope(ActiveScope::class);
    }

    public function modifyDelete($query)
    {
        return $query->withoutGlobalScope(ActiveScope::class);
    }

    public function modifyUpdate($query)
    {
        return $query->withoutGlobalScope(ActiveScope::class);
    }

    public function stored(Employee $employee)
    {
        // Handle country field if provided
        $this->handleCountryField($employee);

        $employeeDetail = request()->all('employee_detail')['employee_detail'];
        $employee->employeeDetail()->create($employeeDetail);

        // To add custom fields data
        if (request()->get('custom_fields_data')) {
            $employee->employeeDetail()->updateCustomFieldData(request()->get('custom_fields_data'));
        }

        $employeeRole = Role::where('name', 'employee')->first();
        $employee->attachRole($employeeRole);

        return $employee;
    }

    public function updating(Employee $employee)
    {
        // Handle country field if provided
        $this->handleCountryField($employee);

        $requestEmployeeDetail = request()->all('employee_detail');

        if ($requestEmployeeDetail && isset($requestEmployeeDetail['employee_detail'])) {
            $data = $requestEmployeeDetail['employee_detail'];

            // Only map and update when provided
            if (isset($data['department']['id'])) {
                $data['department_id'] = $data['department']['id'];
            }

            if (isset($data['designation']['id'])) {
                $data['designation_id'] = $data['designation']['id'];
            }

            unset($data['designation']);
            unset($data['department']);

            if (!empty($data)) {
                $employee->employeeDetail()->update($data);
            }
        }

        // Handle avatar image updates
        if (request()->image_delete == 'yes') {
            Files::deleteFile($employee->image, 'avatar');
            $employee->image = null;
        }

        if (request()->hasFile('image')) {
            Files::deleteFile($employee->image, 'avatar');
            $employee->image = Files::uploadLocalOrS3(request()->file('image'), 'avatar', 300);
        }

        // Persist image/country updates on user
        if ($employee->isDirty('image') || $employee->isDirty('country_id')) {
            $employee->save();
        }

        return $employee;
    }

    /**
     * Handle country field - convert ISO or name to country_id
     */
    private function handleCountryField(Employee $employee)
    {
        $request = request();
        
        // Check if country_iso is provided
        if ($request->has('country_iso')) {
            $country = Country::where('iso', $request->get('country_iso'))->first();
            if ($country) {
                $employee->country_id = $country->id;
                $employee->save();
            }
        }
        
        // Check if country_name is provided
        if ($request->has('country_name')) {
            $country = Country::where('name', $request->get('country_name'))->first();
            if ($country) {
                $employee->country_id = $country->id;
                $employee->save();
            }
        }
    }

    //phpcs:ignore
    public function lastEmployeeID()
    {
        $lastEmployeeID = EmployeeDetails::max('id');

        return ApiResponse::make(null, ['id' => $lastEmployeeID]);
    }

    public function me()
    {
        app()->make($this->indexRequest);

        $query = $this->parseRequest()
            ->addIncludes()
            ->getQuery();

        $employee = $query->withoutGlobalScope(ActiveScope::class)
            ->where('users.id', auth()->id())
            ->firstOrFail();

        return ApiResponse::make(null, ['data' => $employee]);
    }
}
