<?php

namespace Modules\RestAPI\Http\Controllers;

use App\Models\ClientCategory;
use App\Models\ClientSubCategory;
use App\Models\Role;
use Modules\RestAPI\Entities\Client;
use Modules\RestAPI\Http\Requests\Client\CreateRequest;
use Modules\RestAPI\Http\Requests\Client\DeleteRequest;
use Modules\RestAPI\Http\Requests\Client\IndexRequest;
use Modules\RestAPI\Http\Requests\Client\ShowRequest;
use Modules\RestAPI\Http\Requests\Client\UpdateRequest;

class ClientController extends ApiBaseController
{
    protected $model = Client::class;

    protected $indexRequest = IndexRequest::class;

    protected $storeRequest = CreateRequest::class;

    protected $updateRequest = UpdateRequest::class;

    protected $showRequest = ShowRequest::class;

    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query)
    {
        return $query->visibility();
    }

    public function stored(Client $client)
    {
        $clientDetailData = request()->all('client_detail');
        $clientDetail = $clientDetailData['client_detail'] ?? [];

        if (!empty($clientDetail)) {
            $data = $clientDetail;

            // Validate and set category_id
            $categoryId = $data['category']['id'] ?? null;
            if ($categoryId) {
                $category = ClientCategory::where('id', $categoryId)
                    ->where('company_id', $client->company_id)
                    ->first();
                $data['category_id'] = $category ? $category->id : null;
            } else {
                $data['category_id'] = null;
            }

            // Validate and set sub_category_id
            $subCategoryId = $data['sub_category']['id'] ?? null;
            if ($subCategoryId) {
                $subCategory = ClientSubCategory::where('id', $subCategoryId)
                    ->where('company_id', $client->company_id)
                    ->first();
                $data['sub_category_id'] = $subCategory ? $subCategory->id : null;
            } else {
                $data['sub_category_id'] = null;
            }

            //
            $website_url = $data['website'] ?? null;

            if ($website_url) {
                $data['website'] = $website_url;
            } else {
                $data['website'] = null;
            }

            $companyAddress = $data['company_address'] ?? null;
            if ($companyAddress) {
                $data['company_address_id'] = $companyAddress ? $companyAddress->id : null;
            } else {
                $data['company_address_id'] = null;
            }

            unset($data['category']);
            unset($data['sub_category']);
            $client->clientDetail()->create($data);
        } else {
            // Create empty client detail if not provided
            $client->clientDetail()->create([]);
        }

        // Assign client role and permissions
        // Ensure company_id is set on client if not already set (from authenticated API user)
        // Some execution paths may not bootstrap module helpers (start.php),
        // so guard `api_user()` and fall back to the default auth user.
        $apiUser = \function_exists('api_user') ? \api_user() : auth()->user();
        if (!$client->company_id && $apiUser) {
            /** @var \App\Models\User $apiUser */
            $client->company_id = $apiUser->company_id ?? null;
            if ($client->company_id) {
                $client->save();
            }
        }

        // Get company_id for role query (from client or authenticated API user)
        $companyId = $client->company_id;
        if (!$companyId && $apiUser) {
            /** @var \App\Models\User $apiUser */
            $companyId = $apiUser->company_id ?? null;
        }

        // Query role - explicitly filter by company_id to ensure we get the correct role
        // The Role model has HasCompany trait, but in REST API context, company() helper may not work correctly
        $role = Role::where('name', 'client')
            ->when($companyId, function($query) use ($companyId) {
                return $query->where('company_id', $companyId);
            })
            ->select('id')
            ->first();

        if ($role) {
            $client->attachRole($role->id);
            $client->assignUserRolePermission($role->id);
        }

        // To add custom fields data
        if (request()->get('custom_fields_data')) {
            $client->clientDetail()->updateCustomFieldData(request()->get('custom_fields_data'));
        }

        return $client;
    }

    public function updating(Client $client)
    {
        $data = request()->all('client_detail')['client_detail'];
        $data['category_id'] = $data['category']['id'] ?? null;
        $data['sub_category_id'] = $data['sub_category']['id'] ?? null;
        unset($data['category']);
        unset($data['sub_category']);
        $client->client_details()->update($data);

        return $client;
    }
}
