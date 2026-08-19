<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "<pre>";
try {
    echo "Running module:migrate...\n";
    $exitCode = Illuminate\Support\Facades\Artisan::call('module:migrate', ['--force' => true]);
    echo "Exit code: $exitCode\n";
    echo "Output:\n" . Illuminate\Support\Facades\Artisan::output() . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
echo "</pre>";
