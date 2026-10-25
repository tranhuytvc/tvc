<?php

namespace Tests\Feature;

use App\Models\Checkin;
use App\Models\Guest;
use App\Models\ScanOperation;
use App\Services\QrScanService;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class WelcomeScanConcurrencyTest extends TestCase
{
    private string $database;

    protected function setUp(): void
    {
        parent::setUp();
        // Never migrate a developer or production DB. Each test owns a temporary SQLite file.
        $this->database = tempnam(sys_get_temp_dir(), 'qr-concurrency-');
        config([
            'app.key' => 'base64:'.base64_encode(str_repeat('q', 32)),
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $this->database,
            'database.connections.sqlite.busy_timeout' => 5000,
        ]);
        DB::purge('sqlite');
        $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        foreach (glob($this->database.'*') as $path) {
            unlink($path);
        }
        parent::tearDown();
    }

    public static function modes(): array
    {
        return [
            'one_time' => ['one_time'],
            'checkin_checkout' => ['checkin_checkout'],
            'max_scans' => ['max_scans'],
            'unlimited' => ['unlimited'],
        ];
    }

    #[DataProvider('modes')]
    public function test_parallel_retries_commit_exactly_one_operation(string $mode): void
    {
        $guest = Guest::create(['name' => 'Concurrent guest', 'scan_mode' => $mode, 'max_scan_count' => 1]);
        $token = app(QrScanService::class)->confirmation($guest->fresh(), null);
        $results = $this->race($guest, [$token, $token]);
        $this->assertSame([303, 303], array_column($results, 'status'));
        $this->assertSame($results[0]['receipt'], $results[1]['receipt']);
        $this->assertSame(1, $guest->fresh()->scan_count);
        $this->assertSame(1, Checkin::count());
        $this->assertSame(1, ScanOperation::count());
        $this->assertNull($guest->fresh()->latestCheckin->checkout_at);
    }

    #[DataProvider('modes')]
    public function test_parallel_distinct_confirmations_cannot_consume_two_scans(string $mode): void
    {
        $guest = Guest::create(['name' => 'Concurrent guest', 'scan_mode' => $mode, 'max_scan_count' => 1]);
        $scans = app(QrScanService::class);
        $results = $this->race($guest, [
            $scans->confirmation($guest->fresh(), null),
            $scans->confirmation($guest->fresh(), null),
        ]);
        $statuses = array_column($results, 'status');
        sort($statuses);
        $this->assertSame([303, in_array($mode, ['one_time', 'max_scans']) ? 403 : 409], $statuses);
        $this->assertSame(1, $guest->fresh()->scan_count);
        $this->assertSame(1, Checkin::count());
        $this->assertSame(1, ScanOperation::count());
        $this->assertNull($guest->fresh()->latestCheckin->checkout_at);
    }

    private function race(Guest $guest, array $tokens): array
    {
        $barrier = $this->database.'.start';
        $processes = [];
        try {
            foreach ($tokens as $i => $token) {
                $process = new Process([
                    PHP_BINARY, base_path('tests/Support/scan-worker.php'), $this->database,
                    config('app.key'), $guest->qr_code, $token, $barrier, $this->database.'.ready'.$i,
                ], base_path(), ['APP_ENV' => 'testing']);
                $process->setTimeout(20);
                $process->start();
                $processes[] = $process;
            }
            $deadline = microtime(true) + 10;
            while (count(glob($this->database.'.ready*')) < count($tokens)) {
                if (microtime(true) > $deadline) {
                    $this->fail('Scan workers did not reach the concurrency barrier');
                }
                usleep(10000);
            }
            touch($barrier);
            $results = [];
            foreach ($processes as $process) {
                $process->wait();
                $this->assertTrue($process->isSuccessful(), $process->getErrorOutput().$process->getOutput());
                $results[] = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
            }

            return $results;
        } finally {
            foreach ($processes as $process) {
                if ($process->isRunning()) {
                    $process->stop();
                }
            }
        }
    }
}
