<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Business;
use App\Services\BusinessHoursService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class BusinessHoursServiceTest extends TestCase
{
    use RefreshDatabase;

    private BusinessHoursService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new BusinessHoursService;
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    #[Test]
    public function it_returns_open_now_when_current_time_is_within_todays_hours(): void
    {
        Carbon::setTestNow('2026-05-28 14:30:00'); // Thursday

        $business = Business::factory()->create([
            'working_hours' => [
                'thursday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
            ],
        ]);

        $status = $this->service->statusForBusiness($business);

        $this->assertNotNull($status);
        $this->assertTrue($status['is_open_now']);
        $this->assertSame('Open now', $status['status_label']);
        $this->assertSame('09:00 – 17:00', $status['hours_summary']);
    }

    #[Test]
    public function it_returns_closed_when_current_time_is_before_opening(): void
    {
        Carbon::setTestNow('2026-05-28 08:15:00'); // Thursday

        $business = Business::factory()->create([
            'working_hours' => [
                'thursday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
            ],
        ]);

        $status = $this->service->statusForBusiness($business);

        $this->assertNotNull($status);
        $this->assertFalse($status['is_open_now']);
        $this->assertSame('Closed', $status['status_label']);
        $this->assertSame('09:00 – 17:00', $status['hours_summary']);
    }

    #[Test]
    public function it_returns_closed_when_current_time_is_at_or_after_closing(): void
    {
        Carbon::setTestNow('2026-05-28 17:00:00'); // Thursday

        $business = Business::factory()->create([
            'working_hours' => [
                'thursday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
            ],
        ]);

        $status = $this->service->statusForBusiness($business);

        $this->assertNotNull($status);
        $this->assertFalse($status['is_open_now']);
        $this->assertSame('Closed', $status['status_label']);
    }

    #[Test]
    public function it_returns_closed_all_day_when_today_is_marked_closed(): void
    {
        Carbon::setTestNow('2026-05-28 12:00:00');

        $business = Business::factory()->create([
            'working_hours' => [
                'thursday' => ['open' => '09:00', 'close' => '17:00', 'closed' => true],
            ],
        ]);

        $status = $this->service->statusForBusiness($business);

        $this->assertNotNull($status);
        $this->assertFalse($status['is_open_now']);
        $this->assertTrue($status['closed_all_day']);
        $this->assertNull($status['hours_summary']);
    }

    #[Test]
    public function it_returns_null_when_working_hours_are_not_configured(): void
    {
        $business = Business::factory()->create(['working_hours' => null]);

        $this->assertNull($this->service->statusForBusiness($business));
    }

    #[Test]
    public function it_handles_overnight_hours(): void
    {
        Carbon::setTestNow('2026-05-28 23:30:00'); // Thursday

        $business = Business::factory()->create([
            'working_hours' => [
                'thursday' => ['open' => '22:00', 'close' => '02:00', 'closed' => false],
            ],
        ]);

        $status = $this->service->statusForBusiness($business);

        $this->assertNotNull($status);
        $this->assertTrue($status['is_open_now']);
    }
}
