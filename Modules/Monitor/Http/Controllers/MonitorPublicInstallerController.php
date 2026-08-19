<?php

namespace Modules\Monitor\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Monitor\Services\MonitorInstallerService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MonitorPublicInstallerController extends Controller
{
    public function __construct(
        private readonly MonitorInstallerService $installerService,
    ) {
    }

    public function __invoke(string $filename): BinaryFileResponse
    {
        return $this->installerService->servePublicInstaller($filename);
    }
}
