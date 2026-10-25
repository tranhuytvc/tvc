<?php

namespace Tests\Feature;

use App\Models\Checkin;
use App\Models\Guest;
use App\Models\ScanOperation;
use App\Models\Station;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class WelcomeScanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('q', 32))]);
    }

    public static function modes(): array
    {
        return [
            'one time' => ['one_time', 1],
            'in and out' => ['checkin_checkout', 2],
            'one allowed scan' => ['max_scans', 1],
            'odd limit' => ['max_scans', 3],
            'even limit' => ['max_scans', 4],
            'unlimited' => ['unlimited', null],
        ];
    }

    #[DataProvider('modes')]
    public function test_get_head_reload_and_preview_never_record_scans(string $mode, ?int $limit): void
    {
        $guest = $this->guest($mode, $limit);
        $before = $guest->fresh()->getAttributes();
        for ($i = 0; $i < 3; $i++) {
            $this->get($this->url($guest), ['Purpose' => 'prefetch', 'User-Agent' => 'facebookexternalhit/1.1'])
                ->assertOk()->assertSee('XÁC NHẬN CHECK-IN')->assertDontSee('CHECK-IN THÀNH CÔNG');
            $this->head($this->url($guest))->assertOk();
        }
        $this->assertSame($before, $guest->fresh()->getAttributes());
        $this->assertDatabaseCount('checkins', 0);
        $this->assertDatabaseCount('scan_operations', 0);
    }

    #[DataProvider('modes')]
    public function test_modes_limits_and_replays(string $mode, ?int $limit): void
    {
        $guest = $this->guest($mode, $limit);
        $iterations = $limit ?? 5;
        for ($i = 1; $i <= $iterations; $i++) {
            $token = $this->confirmation($guest);
            $response = $this->submit($guest, $token)->assertStatus(303);
            $url = $response->headers->get('Location');
            $action = $i % 2 ? 'checkin' : 'checkout';
            $this->get($url)->assertOk()->assertViewHas('action', $action);
            // HTTP retry and result reload do not consume quota or flip state, including the final scan.
            $this->submit($guest, $token)->assertStatus(303)->assertRedirect($url);
            $this->get($url)->assertOk()->assertViewHas('action', $action);
            $this->assertSame($i, $guest->fresh()->scan_count);
            $this->assertDatabaseCount('scan_operations', $i);
            $this->assertDatabaseCount('checkins', (int) ceil($i / 2));
            $this->assertSame($action === 'checkout', $guest->fresh()->latestCheckin->checkout_at !== null);
            $this->travel(6)->seconds();
        }
        $this->assertSame($limit !== null, $guest->fresh()->is_locked);
        if ($limit !== null) {
            $this->get($this->url($guest))->assertViewIs('blocked');
        }
    }

    public function test_old_receipt_keeps_original_action_after_checkout(): void
    {
        $guest = $this->guest();
        $in = $this->submit($guest, $this->confirmation($guest))->headers->get('Location');
        $this->travel(6)->seconds();
        $this->submit($guest, $this->confirmation($guest))->assertStatus(303);
        $this->get($in)->assertViewHas('action', 'checkin');
        $this->assertSame(2, $guest->fresh()->scan_count);
    }

    public function test_two_tabs_cannot_toggle_with_stale_confirmations(): void
    {
        $guest = $this->guest();
        $first = $this->confirmation($guest);
        $second = $this->confirmation($guest);
        $this->submit($guest, $first)->assertStatus(303);
        $this->travel(6)->seconds();
        $this->submit($guest, $second)->assertStatus(409)->assertSee('Mở lại trang xác nhận');
        $this->assertSame(1, $guest->fresh()->scan_count);
        $this->assertNull($guest->fresh()->latestCheckin->checkout_at);
    }

    public function test_fresh_rapid_confirmation_is_throttled_but_later_checkout_succeeds(): void
    {
        $guest = $this->guest();
        $this->submit($guest, $this->confirmation($guest))->assertStatus(303);
        $token = $this->confirmation($guest);
        $this->submit($guest, $token)->assertStatus(429)->assertHeader('Retry-After', '5')->assertSee('Vừa ghi nhận lượt quét');
        $this->assertSame(1, $guest->fresh()->scan_count);
        $this->travel(6)->seconds();
        $this->submit($guest, $token)->assertStatus(303);
        $this->assertSame(2, $guest->fresh()->scan_count);
    }

    public static function blockedStates(): array
    {
        return [
            'inactive' => [['is_active' => false]],
            'locked' => [['is_locked' => true]],
            'exhausted without lock' => [['scan_mode' => 'max_scans', 'max_scan_count' => 1, 'scan_count' => 1]],
        ];
    }

    #[DataProvider('blockedStates')]
    public function test_post_rechecks_eligibility_after_confirmation_was_opened(array $changes): void
    {
        $guest = $this->guest();
        $token = $this->confirmation($guest);
        $guest->update($changes);
        $this->submit($guest, $token)->assertForbidden();
        $this->get($this->url($guest))->assertViewIs('blocked');
        $this->assertDatabaseCount('checkins', 0);
        $this->assertDatabaseCount('scan_operations', 0);
    }

    public function test_policy_change_invalidates_open_confirmation(): void
    {
        $guest = $this->guest();
        $token = $this->confirmation($guest);
        $guest->update(['scan_mode' => 'max_scans', 'max_scan_count' => 10]);
        $this->submit($guest, $token)->assertStatus(409);
        $this->assertSame(0, $guest->fresh()->scan_count);
    }

    public function test_invalid_expired_or_cross_guest_confirmation_cannot_write(): void
    {
        $guest = $this->guest();
        $token = $this->confirmation($guest);
        $this->submit($guest, 'tampered')->assertStatus(422);
        $this->post($this->url($guest).'/scan')->assertSessionHasErrors('confirmation');
        $this->submit($this->guest(), $token)->assertStatus(422);
        $this->travel(601)->seconds();
        $this->submit($guest, $token)->assertStatus(419);
        $this->assertDatabaseCount('checkins', 0);
        $this->assertDatabaseCount('scan_operations', 0);
    }

    public function test_invalid_qr_and_foreign_receipt_are_not_found(): void
    {
        $guest = $this->guest();
        $token = $this->confirmation($guest);
        $url = $this->submit($guest, $token)->headers->get('Location');
        $this->get(str_replace($guest->qr_code, $this->guest()->qr_code, $url))->assertNotFound();
        $this->get('/welcome/'.Str::uuid())->assertNotFound();
        $this->post('/welcome/'.Str::uuid().'/scan', ['confirmation' => $token])->assertStatus(422);
        $this->assertDatabaseCount('scan_operations', 1);
    }

    public function test_station_is_bound_to_confirmation_and_branding_is_preserved(): void
    {
        $guest = $this->guest();
        $station = Station::create(['name' => 'Cổng A', 'settings' => ['welcome_msg_checkin' => 'Xin chào sự kiện A']]);
        $other = Station::create(['name' => 'Cổng B']);
        $token = $this->get($this->url($guest).'?station='.$station->scan_slug)
            ->assertOk()->assertSee('Xin chào sự kiện A')->viewData('confirmation');
        $url = $this->post($this->url($guest).'/scan', [
            'confirmation' => $token, 'station' => $other->scan_slug, 'action' => 'checkout',
        ])->assertStatus(303)->headers->get('Location');
        $this->get($url)->assertSee('Xin chào sự kiện A')->assertViewHas('action', 'checkin');
        $this->assertSame($station->id, $guest->fresh()->latestCheckin->station_id);
        $this->assertDatabaseHas('scan_operations', ['guest_id' => $guest->id, 'station_id' => $station->id]);
        $this->get('/display/'.$station->display_slug)->assertOk();
        $this->get('/api/station/'.$station->display_slug.'/latest-checkin')->assertOk()->assertJsonPath('name', $guest->name);
    }

    public function test_invalid_or_deactivated_station_does_not_fall_back_to_global(): void
    {
        $guest = $this->guest();
        $this->get($this->url($guest).'?station=unknown')->assertNotFound();
        $station = Station::create(['name' => 'Cổng A']);
        $token = $this->get($this->url($guest).'?station='.$station->scan_slug)->viewData('confirmation');
        $station->update(['is_active' => false]);
        $this->submit($guest, $token)->assertNotFound();
        $this->get($this->url($guest).'?station='.$station->scan_slug)->assertNotFound();
        $this->assertDatabaseCount('checkins', 0);
    }

    public function test_counter_reset_does_not_reenable_old_operations_and_one_time_never_checks_out(): void
    {
        $guest = $this->guest('one_time', 1);
        $old = $this->confirmation($guest);
        $this->submit($guest, $old)->assertStatus(303);
        // Existing CMS reset behavior: reset the quota, preserve history.
        $guest->update(['scan_count' => 0, 'is_locked' => false]);
        $this->submit($guest, $old)->assertStatus(303);
        $this->assertSame(0, $guest->fresh()->scan_count);
        $this->travel(6)->seconds();
        $this->submit($guest, $this->confirmation($guest))->assertStatus(303);
        $this->assertDatabaseCount('checkins', 2);
        $this->assertSame(0, Checkin::whereNotNull('checkout_at')->count());
    }

    public function test_failure_rolls_back_checkin_counter_lock_version_and_receipt(): void
    {
        $guest = $this->guest('one_time', 1);
        $token = $this->confirmation($guest);
        ScanOperation::creating(fn () => throw new \RuntimeException('Simulated database failure'));
        try {
            $this->submit($guest, $token)->assertStatus(500);
        } finally {
            ScanOperation::flushEventListeners();
        }
        $this->assertSame(0, $guest->fresh()->scan_count);
        $this->assertEquals(0, $guest->fresh()->scan_version);
        $this->assertFalse($guest->fresh()->is_locked);
        $this->assertDatabaseCount('checkins', 0);
        $this->assertDatabaseCount('scan_operations', 0);
        $this->submit($guest, $token)->assertStatus(303);
    }

    public function test_csrf_middleware_rejects_missing_and_wrong_token_and_accepts_matching_token(): void
    {
        $guest = $this->guest();
        $token = $this->confirmation($guest);
        // Laravel bypasses CSRF in testing; enable the real middleware behavior here.
        $this->app['env'] = 'production';
        try {
            $this->withSession(['_token' => 'valid-csrf']);
            $this->submit($guest, $token)->assertStatus(419);
            $this->post($this->url($guest).'/scan', ['confirmation' => $token, '_token' => 'wrong'])->assertStatus(419);
            $this->assertDatabaseCount('scan_operations', 0);
            $this->post($this->url($guest).'/scan', ['confirmation' => $token, '_token' => 'valid-csrf'])->assertStatus(303);
        } finally {
            $this->app['env'] = 'testing';
        }
    }

    private function guest(string $mode = 'unlimited', ?int $limit = null): Guest
    {
        return Guest::create(['name' => 'Khách thử nghiệm', 'scan_mode' => $mode, 'max_scan_count' => $limit ?? 2]);
    }

    private function url(Guest $guest): string
    {
        return '/welcome/'.$guest->qr_code;
    }

    private function confirmation(Guest $guest): string
    {
        return $this->get($this->url($guest))->assertOk()->viewData('confirmation');
    }

    private function submit(Guest $guest, string $token)
    {
        return $this->post($this->url($guest).'/scan', ['confirmation' => $token]);
    }
}
