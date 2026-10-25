<?php

use App\Services\QrScanService;
use Illuminate\Contracts\Console\Kernel;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

// Separate PHP process and DB connection, used only by WelcomeScanConcurrencyTest.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
config([
    'app.key' => $argv[2],
    'database.default' => 'sqlite',
    'database.connections.sqlite.database' => $argv[1],
    'database.connections.sqlite.busy_timeout' => 5000,
]);
$deadline = microtime(true) + 10;
file_put_contents($argv[6], 'ready');
while (! file_exists($argv[5])) {
    if (microtime(true) > $deadline) {
        throw new RuntimeException('Concurrency barrier timed out');
    }
    usleep(10000);
}
try {
    $receipt = app(QrScanService::class)->record($argv[3], $argv[4], '127.0.0.1');
    echo json_encode(['status' => 303, 'receipt' => $receipt->request_id]);
} catch (HttpExceptionInterface $e) {
    echo json_encode(['status' => $e->getStatusCode()]);
}
