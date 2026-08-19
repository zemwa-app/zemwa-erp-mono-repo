<?php

namespace Modules\Aitools\Http\Controllers;

use App\Helper\Reply;
use App\Models\Company;
use Modules\Aitools\Entities\AiToolsSetting;
use Modules\Aitools\Entities\AiToolsUsageHistory;
use Modules\Aitools\Traits\ChecksTokenAvailability;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AccountBaseController;

class AiToolsSettingController extends AccountBaseController
{
    use ChecksTokenAvailability;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'aitools::app.aiTools';
        $this->activeSettingMenu = 'ai_tools_settings';
        $this->middleware(function ($request, $next) {
            
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        abort_403(
            !(user()->is_superadmin && module_enabled('Aitools'))
        );
    
        $this->aiToolsSetting = AiToolsSetting::first();
        $this->activeTab = $request->get('tab', 'settings');
        
        // Automatically fetch and save the latest model from API if API key is available
        $apiKey = $this->aiToolsSetting->chatgpt_api_key ?? null;
        if ($apiKey) {
            try {
                // Force refresh to get the latest model
                $latestModel = self::fetchModelsFromAPI($apiKey);
               
                if ($latestModel) {
                    // Save the latest model
                    $this->aiToolsSetting->model_name = $latestModel;
                    $this->aiToolsSetting->save();
                } else {
                    // If fetch returns null, use existing model or set default
                    if (empty($this->aiToolsSetting->model_name)) {
                        $this->aiToolsSetting->model_name = 'gpt-4o-mini';
                        $this->aiToolsSetting->save();
                    }
                }
            } catch (\Exception $e) {
                // If API fetch fails, use existing model or set default
                if (empty($this->aiToolsSetting->model_name)) {
                    $this->aiToolsSetting->model_name = 'gpt-4o-mini';
                    $this->aiToolsSetting->save();
                }
                logger()->warning('Failed to fetch latest model from API', ['error' => $e->getMessage()]);
            }
        } else {
            // Set default model if not set and no API key
            if (empty($this->aiToolsSetting->model_name)) {
                AiToolsSetting::create([
                    'model_name' => 'gpt-4o-mini',
                ]);

            }
        }

        // Get usage statistics from the single usage_history record
        $usageRecord = AiToolsUsageHistory::where('company_id', null)->first();
        $this->totalTokens = (int) ($usageRecord->total_tokens ?? 0);
        $this->totalRequests = (int) ($usageRecord->total_requests ?? 0);
        $this->totalPromptTokens = (int) ($usageRecord->prompt_tokens ?? 0);
        $this->totalCompletionTokens = (int) ($usageRecord->completion_tokens ?? 0);

        // Get usage history records for simple table
        if ($this->activeTab == 'usage') {
            if (user()->is_superadmin && module_enabled('Aitools')) {
                $this->usageHistory = AiToolsUsageHistory::withoutGlobalScope(\App\Scopes\CompanyScope::class)
                    ->with(['user', 'company'])
                    ->orderBy('created_at', 'desc')
                    ->paginate(companyOrGlobalSetting()->datatable_row_limit ?? 10);
                
                // Get company-wise token statistics
                $companyFilter = $request->get('company_id', 'all');
                $this->companyTokenStats = $this->getCompanyTokenStatistics($companyFilter);
                
                // Get all companies for filter dropdown
                $this->companies = Company::withoutGlobalScope(\App\Scopes\CompanyScope::class)
                    ->orderBy('company_name', 'asc')
                    ->get();
            } else {
                $companyId = company()->id;
                $this->usageHistory = AiToolsUsageHistory::where('company_id', $companyId)
                    ->with(['user', 'company'])
                    ->orderBy('created_at', 'desc')
                    ->paginate(companyOrGlobalSetting()->datatable_row_limit ?? 10);
            }
        }

        return view('aitools::ai-tools-settings.index', $this->data);
    }


    /**
     * Fetch latest model ID from OpenAI API
     *
     * @param string $apiKey
     * @return string|null
     * @throws \Exception
     */
    private static function fetchModelsFromAPI($apiKey)
    {
        $client = new Client();
        
        try {
            $response = $client->request('GET', 'https://api.openai.com/v1/models', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'timeout' => 10,
            ]);

            $responseBody = json_decode($response->getBody(), true);
            $models = [];

            if (isset($responseBody['data']) && is_array($responseBody['data'])) {
                // Filter and format models - only include GPT models suitable for chat completions
                foreach ($responseBody['data'] as $model) {
                    $modelId = $model['id'] ?? '';
                   
                    
                    // Only include GPT models that support chat completions
                    // Exclude vision, instruct, embedding, moderation, audio, and whisper models
                    if (strpos($modelId, 'gpt-') === 0 && 
                        strpos($modelId, 'vision') === false && 
                        strpos($modelId, 'instruct') === false &&
                        strpos($modelId, 'embedding') === false &&
                        strpos($modelId, 'moderation') === false &&
                        strpos($modelId, 'audio') === false &&
                        strpos($modelId, 'whisper') === false) {
                        
                        // Store model with its created timestamp for sorting
                        $created = $model['created'] ?? 0;
                        $models[$modelId] = [
                            'id' => $modelId,
                            'created' => $created,
                            'label' => $modelId
                        ];
                    }
                }
                
                // now remove the date from the model id and get the hight decimal model after that
                $models = array_map(function($model) {
                    return [
                       'id' => preg_replace('/^((?:[^-]+-){1}[^-]+).*/', '$1', $model['id']),
                        'created' => $model['created'],
                        'label' => $model['id']
                    ];
                }, $models);

               
                $models = array_map(function ($model) {

                    $id = $model['id'];

                    // Case 1: Numeric GPT versions (gpt-5.3, gpt-4.5)
                    if (preg_match('/^(gpt-\d+\.\d+)/', $id, $m)) {
                        $normalizedId = $m[1];
                    }
                    // Case 2: String-based GPT models → force gpt-1.1
                    elseif (preg_match('/^gpt-[a-z]+/', $id)) {
                        $normalizedId = 'gpt-1.1';
                    }
                    else {
                        $normalizedId = $id;
                    }
                
                    return [
                        'id'      => $normalizedId,
                        'created' => $model['created'],
                        'label'   => $model['id'],
                    ];
                }, $models);

                
                
                uksort($models, function ($a, $b) use ($models) {

                    // Extract numeric version from id (gpt-4, gpt-3.5, gpt-1.1)
                    preg_match('/gpt-(\d+(?:\.\d+)?)/', $models[$a]['id'], $va);
                    preg_match('/gpt-(\d+(?:\.\d+)?)/', $models[$b]['id'], $vb);
                
                    $versionA = $va[1] ?? 0;
                    $versionB = $vb[1] ?? 0;
                
                    // Descending order (highest first)
                    return version_compare($versionB, $versionA);
                });
                
                if (!empty($models)) {
                    // dd(reset($models)['id']);
                    return reset($models)['id'];
                }
               
            }
            
            // If API returned successfully but no suitable models found, throw exception
            throw new \Exception('No suitable GPT models found in API response');
            
        } catch (RequestException $e) {
            $errorMessage = 'Failed to fetch models from OpenAI API.';
            if ($e->hasResponse()) {
                try {
                    $errorResponse = json_decode($e->getResponse()->getBody(), true);
                    if (isset($errorResponse['error']['message'])) {
                        $errorMessage = $errorResponse['error']['message'];
                    }
                } catch (\Exception $parseException) {
                    // Use default message
                }
            }
            throw new \Exception($errorMessage);
        } catch (GuzzleException $e) {
            throw new \Exception('Network error while fetching models: ' . $e->getMessage());
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id-
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $setting = AiToolsSetting::first();
        
        // Only update if a value is provided (to allow clearing the field)
        if ($request->has('chatgpt_api_key')) {
            $setting->chatgpt_api_key = $request->chatgpt_api_key ?: null;
        }
        
        // Update model name
        if ($request->has('model_name')) {
            $setting->model_name = $request->model_name ?: 'gpt-4o-mini';
        }
        
        $setting->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Test ChatGPT API with a prompt
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function testChat(Request $request)
    {
        // Check token availability before processing (skip for superadmin)
        if (!user()->is_superadmin) {
            $tokenCheck = $this->checkTokenAvailability();
            if ($tokenCheck !== true) {
                return $tokenCheck;
            }
        }
        
        $setting = AiToolsSetting::first();

        if (empty($setting->chatgpt_api_key)) {
            return Reply::error(__('aitools::messages.apiKeyRequired'));
        }

        $prompt = $request->input('prompt');
        if (empty($prompt)) {
            return Reply::error(__('aitools::messages.promptRequired'));
        }

        try {
            $client = new Client();
            
            // Get the selected model or use default
            $model = $setting->model_name ?: 'gpt-4o-mini';
            
            $response = $client->request('POST', 'https://api.openai.com/v1/responses', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $setting->chatgpt_api_key,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model' => $model,
                    'input' => [
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'input_text',
                                    'text' => $prompt
                                ]
                            ]
                        ]
                    ],
                ],
                'timeout' => 30,
            ]);
            

            $responseBody = json_decode($response->getBody(), true);

            $reply = $responseBody['output'][0]['content'][0]['text'] ?? null;

            if (!$reply) {
                logger()->error('OpenAI unexpected response', $responseBody);
                return Reply::error(__('aitools::messages.unexpectedResponse'));
            }

            // Track usage - try multiple possible response formats
            $usage = $responseBody['usage'] ?? $responseBody['data']['usage'] ?? [];
           
            // Try different possible locations for usage data
            $promptTokens = $usage['prompt_tokens'] ?? $usage['input_tokens'] ?? $usage['n_context_tokens_total'] ?? 0;
            $completionTokens = $usage['completion_tokens'] ?? $usage['output_tokens'] ?? $usage['n_generated_tokens_total'] ?? 0;
            $totalTokens = $usage['total_tokens'] ?? ($promptTokens + $completionTokens);
            
            // If still no tokens, estimate based on prompt and response length
            if ($totalTokens == 0) {
                // Rough estimation: ~4 characters per token for English text
                $promptTokens = max(1, (int) ceil(mb_strlen($prompt) / 4));
                $completionTokens = max(1, (int) ceil(mb_strlen($reply) / 4));
                $totalTokens = $promptTokens + $completionTokens;
            }

            // Track usage - update single record instead of creating new ones
            try {

                if(user()->is_superadmin && module_enabled('Aitools')){
                    // where company_id id is null
                    $companyId = null;
                    $usageRecord = AiToolsUsageHistory::where('company_id', null)->first();
                }else{

                    $companyId = company()->id;
                    $usageRecord = AiToolsUsageHistory::where('company_id', $companyId)->first();
                }

                if ($usageRecord) {
                    // Update existing record by adding to current values
                    $usageRecord->update([
                        'model' => $model,
                        'prompt' => $prompt,
                        'response' => mb_substr($reply, 0, 1000),
                        'prompt_tokens' => ($usageRecord->prompt_tokens ?? 0) + $promptTokens,
                        'completion_tokens' => ($usageRecord->completion_tokens ?? 0) + $completionTokens,
                        'total_tokens' => ($usageRecord->total_tokens ?? 0) + $totalTokens,
                        'total_requests' => ($usageRecord->total_requests ?? 0) + 1,
                    ]);
                } else {
                    // Create first record
                    AiToolsUsageHistory::create([
                        'company_id' => $companyId,
                        'user_id' => user()->id,
                        'model' => $model,
                        'prompt' => $prompt,
                        'response' => mb_substr($reply, 0, 1000),
                        'prompt_tokens' => $promptTokens,
                        'completion_tokens' => $completionTokens,
                        'total_tokens' => $totalTokens,
                        'total_requests' => 1,
                    ]);
                }
            } catch (\Exception $e) {
                // Log error but don't fail the request
                logger()->error('Failed to save usage history', [
                    'error' => $e->getMessage(),
                    'prompt_tokens' => $promptTokens,
                    'completion_tokens' => $completionTokens,
                ]);
            }

            return Reply::dataOnly([
                'status' => 'success',
                'response' => $reply
            ]);
            

        } catch (RequestException $e) {
            $errorMessage = 'An error occurred while connecting to OpenAI API.';
            
            // Try to parse error response
            try {
                if ($e->hasResponse()) {
                    $errorResponse = json_decode($e->getResponse()->getBody(), true);
                    if (isset($errorResponse['error']['message'])) {
                        $errorMessage = $errorResponse['error']['message'];
                    } elseif (isset($errorResponse['error'])) {
                        $errorMessage = is_string($errorResponse['error']) ? $errorResponse['error'] : 'API Error: ' . json_encode($errorResponse['error']);
                    }
                } else {
                    // Network or connection error
                    $errorMessage = 'Unable to connect to OpenAI API. Please check your internet connection and API key.';
                }
            } catch (\Exception $parseException) {
                // If parsing fails, use default message
                $errorMessage = 'Unable to connect to OpenAI API. Please check your internet connection and API key.';
            }
            
            return Reply::error($errorMessage);
        } catch (GuzzleException $e) {
            return Reply::error('Network error: Unable to connect to OpenAI API. Please check your internet connection.');
        } catch (\Exception $e) {
            return Reply::error('An unexpected error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Refresh usage data - recalculates statistics from database
     *
     * @return \Illuminate\Http\Response
     */
    public function refreshUsage()
    {
        try {

            if(user()->is_superadmin && module_enabled('Aitools')){
                // where company_id id is null
                $companyId = null;
                $usageRecord = AiToolsUsageHistory::where('company_id', null)->first();
            }else{

                $companyId = company()->id;
                $usageRecord = AiToolsUsageHistory::where('company_id', $companyId)->first();
            }
            
            $totalTokens = (int) ($usageRecord->total_tokens ?? 0);
            $totalRequests = (int) ($usageRecord->total_requests ?? 0);
            $totalPromptTokens = (int) ($usageRecord->prompt_tokens ?? 0);
            $totalCompletionTokens = (int) ($usageRecord->completion_tokens ?? 0);

            $message = __('aitools::app.usageDataRefreshedSuccessfully');

            return Reply::successWithData($message, [
                'total_tokens' => $totalTokens,
                'total_requests' => $totalRequests,
                'total_prompt_tokens' => $totalPromptTokens,
                'total_completion_tokens' => $totalCompletionTokens,
            ]);

        } catch (\Exception $e) {
            return Reply::error(__('aitools::app.errorRefreshingUsageData') . ': ' . $e->getMessage());
        }
    }

    /**
     * Reset usage data - deletes the usage history record to start fresh
     *
     * @return \Illuminate\Http\Response
     */
    public function resetUsage()
    {
        try {

            if(user()->is_superadmin && module_enabled('Aitools')){
                // where company_id id is null
                $companyId = null;
                $usageRecord = AiToolsUsageHistory::where('company_id', null)->delete();
            }else{

                $companyId = company()->id;
                $usageRecord = AiToolsUsageHistory::where('company_id', $companyId)->delete();
            }
            
            // Delete the usage history record for this company
            AiToolsUsageHistory::where('company_id', $companyId)->delete();

            $message = __('aitools::app.usageDataResetSuccessfully');

            return Reply::successWithData($message, [
                'total_tokens' => 0,
                'total_requests' => 0,
                'total_prompt_tokens' => 0,
                'total_completion_tokens' => 0,
            ]);

        } catch (\Exception $e) {
            return Reply::error(__('aitools::app.errorResettingUsageData') . ': ' . $e->getMessage());
        }
    }

    /**
     * Display company-level usage and history page
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function companyUsage(Request $request)
    {
        abort_403(
            !(module_enabled('Aitools') && in_array('aitools', user_modules()) && user()->permission('edit_aitools') == 'all')
        );

        $this->pageTitle = 'aitools::app.usageHistory';
        $this->activeSettingMenu = 'ai_tools_usage';

        $companyId = company()->id;

        // Get company usage statistics
        $usageHistoryRecords = AiToolsUsageHistory::where('company_id', $companyId)->get();
        
        $this->totalTokens = $usageHistoryRecords->sum('total_tokens');
        $this->totalRequests = $usageHistoryRecords->sum('total_requests');
        $this->totalPromptTokens = $usageHistoryRecords->sum('prompt_tokens');
        $this->totalCompletionTokens = $usageHistoryRecords->sum('completion_tokens');

        // Get package tokens and calculate remaining tokens
        $company = company();
        $package = $company->package;
        $this->totalAssignedTokens = $package ? ($package->ai_chatgpt_tokens ?? 0) : 0;
        $this->remainingTokens = max(0, $this->totalAssignedTokens - $this->totalTokens);

        // Get paginated usage history records
        $this->usageHistory = AiToolsUsageHistory::where('company_id', $companyId)
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->paginate(companyOrGlobalSetting()->datatable_row_limit ?? 10);

        return view('aitools::ai-tools-usage.index', $this->data);
    }

    /**
     * Get company-wise token statistics
     *
     * @param string $companyFilter
     * @return \Illuminate\Support\Collection
     */
    private function getCompanyTokenStatistics($companyFilter = 'all')
    {
        $query = Company::withoutGlobalScope(\App\Scopes\CompanyScope::class)
            ->with('package')
            ->select('companies.id', 'companies.company_name', 'companies.package_id')
            ->whereNotNull('companies.package_id');

        // Apply company filter
        if ($companyFilter !== 'all' && $companyFilter) {
            $query->where('companies.id', $companyFilter);
        }

        $companies = $query->get();

        $stats = $companies->map(function ($company) {
            // Get total assigned tokens from package
            $totalAssignedTokens = $company->package ? ($company->package->ai_chatgpt_tokens ?? 0) : 0;

            // Get total tokens used by company
            $usageHistoryRecords = AiToolsUsageHistory::where('company_id', $company->id)->get();
            $totalTokensUsed = $usageHistoryRecords->sum('total_tokens');

            // Calculate remaining tokens
            $remainingTokens = max(0, $totalAssignedTokens - $totalTokensUsed);

            return [
                'company_id' => $company->id,
                'company_name' => $company->company_name,
                'total_tokens' => $totalAssignedTokens,
                'used_tokens' => $totalTokensUsed,
                'remaining_tokens' => $remainingTokens,
            ];
        });

        return $stats;
    }

    /**
     * Update company used tokens
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function updateCompanyUsedTokens(Request $request)
    {
        abort_403(!(user()->is_superadmin && module_enabled('Aitools')));

        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'used_tokens' => 'required|integer|min:0',
        ]);

        try {
            $companyId = $request->company_id;
            $newUsedTokens = (int) $request->used_tokens;

            // Get all usage history records for the company
            $usageHistoryRecords = AiToolsUsageHistory::where('company_id', $companyId)->get();
            $currentTotalUsed = $usageHistoryRecords->sum('total_tokens');

            // Calculate the difference
            $difference = $newUsedTokens - $currentTotalUsed;

            if ($difference != 0) {
                // Get or create the usage history record
                $usageRecord = AiToolsUsageHistory::where('company_id', $companyId)->first();

                if ($usageRecord) {
                    // Update existing record
                    $newTotalTokens = max(0, $usageRecord->total_tokens + $difference);
                    $usageRecord->update([
                        'total_tokens' => $newTotalTokens,
                    ]);
                } else {
                    // Create new record if none exists
                    $company = Company::find($companyId);
                    AiToolsUsageHistory::create([
                        'company_id' => $companyId,
                        'user_id' => $company->user_id ?? null,
                        'model' => 'manual-update',
                        'prompt' => 'Manual token adjustment by superadmin',
                        'response' => '',
                        'prompt_tokens' => 0,
                        'completion_tokens' => 0,
                        'total_tokens' => $newUsedTokens,
                        'total_requests' => 0,
                    ]);
                }
            }

            // Get updated statistics
            $company = Company::with('package')->find($companyId);
            $totalAssignedTokens = $company->package ? ($company->package->ai_chatgpt_tokens ?? 0) : 0;
            $updatedUsageRecords = AiToolsUsageHistory::where('company_id', $companyId)->get();
            $totalTokensUsed = $updatedUsageRecords->sum('total_tokens');
            $remainingTokens = max(0, $totalAssignedTokens - $totalTokensUsed);

            return Reply::successWithData(__('messages.updateSuccess'), [
                'used_tokens' => $totalTokensUsed,
                'remaining_tokens' => $remainingTokens,
                'total_assigned_tokens' => $totalAssignedTokens,
            ]);

        } catch (\Exception $e) {
            return Reply::error(__('messages.somethingWentWrong') . ': ' . $e->getMessage());
        }
    }
}

