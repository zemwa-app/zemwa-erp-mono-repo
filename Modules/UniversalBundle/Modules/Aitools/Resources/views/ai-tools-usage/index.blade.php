@extends('layouts.app')

@push('styles')
    <style>
        .ai-tool-card {
            background: #fff;
            border: 1px solid #e0e6ed;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 24px;
            transition: all 0.3s ease;
        }

        .ai-tool-card:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .usage-stat-card {
            padding: 20px;
        }

        .usage-stat-card h3 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .usage-stat-card p {
            font-size: 14px;
            color: #6c757d;
        }
    </style>
@endpush

@section('content')

    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">

        <x-setting-sidebar :activeMenu="$activeSettingMenu" />

        <x-setting-card>
            <x-slot name="header">
                {{-- <div class="s-b-n-header">
                    <h4 class="mb-0 f-18 font-weight-normal">@lang('aitools::app.usageHistory')</h4>
                </div> --}}
            </x-slot>

            <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-20">
                {{-- Usage Statistics --}}
                <div class="row">
                    <div class="col-lg-12 mb-4">
                        <h5 class="mb-4">@lang('aitools::app.usageStatistics')</h5>
                        
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="ai-tool-card usage-stat-card text-center">
                                    <h3 class="mb-2 text-info" id="stat-prompt-tokens">{{ number_format($totalPromptTokens ?? 0) }}</h3>
                                    <p class="mb-0 text-muted">@lang('aitools::app.promptTokens')</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="ai-tool-card usage-stat-card text-center">
                                    <h3 class="mb-2 text-warning" id="stat-completion-tokens">{{ number_format($totalCompletionTokens ?? 0) }}</h3>
                                    <p class="mb-0 text-muted">@lang('aitools::app.completionTokens')</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="ai-tool-card usage-stat-card text-center">
                                    <h3 class="mb-2 text-success" id="stat-total-requests">{{ number_format($totalRequests ?? 0) }}</h3>
                                    <p class="mb-0 text-muted">@lang('aitools::app.totalRequests')</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="ai-tool-card usage-stat-card text-center">
                                    <h3 class="mb-2 text-primary" id="stat-total-tokens">{{ number_format($totalTokens ?? 0) }}</h3>
                                    <p class="mb-0 text-muted">@lang('aitools::app.totalTokens')</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="ai-tool-card usage-stat-card text-center">
                                    <h3 class="mb-2 text-dark" id="stat-total-assigned-tokens">{{ number_format($totalAssignedTokens ?? 0) }}</h3>
                                    <p class="mb-0 text-muted">@lang('aitools::app.totalAssignedTokens')</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="ai-tool-card usage-stat-card text-center">
                                    <h3 class="mb-2 {{ ($remainingTokens ?? 0) > 0 ? 'text-success' : 'text-danger' }}" id="stat-remaining-tokens">{{ number_format($remainingTokens ?? 0) }}</h3>
                                    <p class="mb-0 text-muted">@lang('aitools::app.remainingTokens')</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
            </div>
        </x-setting-card>
    </div>
    <!-- SETTINGS END -->

@endsection

@push('scripts')
    <script>
        function number_format(number) {
            return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }
    </script>
@endpush
