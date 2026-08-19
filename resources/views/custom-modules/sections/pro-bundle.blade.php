@if ($proBundle)
    <div class='card border mb-4'>
        <div class="card-body bg-white border-0 pt-4">
            <div class="row">
                @php
                    $fetchSettingPro = null;
                    if (in_array($proBundle, $worksuitePlugins) && config(strtolower($proBundle) . '.setting')) {
                        $fetchSettingPro = config(strtolower($proBundle) . '.setting')::first();
                    }
                @endphp

                <div class="col-md-6">
                    <div class="d-flex">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 148.319 148.319">
                            <g id="Group_1192" data-name="Group 1192" transform="translate(0 0)">
                                <path id="Path_1092" data-name="Path 1092" d="M222.36,482h8.691v8.691H222.36Z" transform="translate(-157.945 -342.371)" fill="#4d5bf9" />
                                <path id="Path_1093" data-name="Path 1093" d="M0,259.64H8.691v8.691H0Z" transform="translate(0 -184.426)" fill="#8b94f5" />
                                <path id="Path_1094" data-name="Path 1094" d="M87.9,343.049H96.6v8.691H87.9ZM24.383,368.021c10.842-10.485,22.345,1.1,22.345,1.1,30.347-8.212,29.264-38.96,29.264-38.96L64.876,310.85,20.835,363.085A21.736,21.736,0,0,0,24.383,368.021Z" transform="translate(-14.799 -220.801)" fill="#4d5bf9" />
                                <path id="Path_1095" data-name="Path 1095" d="M.755,329.786l8.483,5.51c-7.059,8.145-5.2,15.831-2.667,20.6L54.71,307.76,39.5,300.74s-30.53-1.3-38.741,29.046Z" transform="translate(-0.536 -213.614)" fill="#8b94f5" />
                                <path id="Path_1096" data-name="Path 1096" d="M138.662,330.161l-11.117-19.31-18.483,26.676A32.686,32.686,0,0,0,138.662,330.161Z" transform="translate(-77.469 -220.802)" fill="#1c2541" />
                                <path id="Path_1097" data-name="Path 1097" d="M106.7,330.359l22.579-22.579-15.213-7.02A32.686,32.686,0,0,0,106.7,330.359Z" transform="translate(-75.108 -213.634)" fill="#3a506b" />
                                <path id="Path_1098" data-name="Path 1098" d="M172.865,302.977l11.117,11.117,6.553-6.553-11.117-19.31Z" transform="translate(-122.788 -204.734)" fill="#cfcfe6" />
                                <path id="Path_1099" data-name="Path 1099" d="M141.043,278.14l-6.552,6.553,11.116,11.117,10.649-10.649Z" transform="translate(-95.531 -197.567)" fill="#ece6f2" />
                            </g>
                        </svg>

                        <div class="">
                            <span class="h5 mb-0 mx-2">{{ $proBundle->getName() }}</span>
                            @if (config(strtolower($proBundle) . '.setting'))
                                @include('custom-modules.sections.version', ['module' => $proBundle])
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6 d-flex align-items-center justify-content-end">
                    <div class="">
                        @if ($fetchSettingPro)
                            @if (config(strtolower($proBundle) . '.verification_required'))
                                <div class="d-flex align-items-center justify-content-end">
                                     @include('custom-modules.sections.purchase-code', ['module' => $proBundle, 'fetchSetting' => $fetchSettingPro])
                                </div>
                            @endif
                        @endif
                    </div>
                    @if (!config(strtolower($proBundle) . '.name'))
                        <div class="custom-control custom-switch ml-2" data-toggle="tooltip"
                            data-original-title="@lang('app.moduleSwitchMessage', ['name' => $proBundle])">
                            <input type="checkbox" class="custom-control-input change-module-status"
                                id="module-{{ $proBundle }}" data-module-name="{{ $proBundle }}">
                            <label class="custom-control-label cursor-pointer"
                                for="module-{{ $proBundle }}"></label>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @includeIf('probundle::install-modules')
@endif
