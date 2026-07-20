<?php

declare(strict_types=1);

namespace Tests\Unit\Quiz;

use App\Models\Quiz;
use App\Services\Quiz\JoinQrCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class JoinQrCodeTest extends TestCase
{
    use RefreshDatabase;

    private function quiz(): Quiz
    {
        return Quiz::factory()->make(['code' => 'RPRF3']);
    }

    public function test_the_encoded_url_is_the_short_one(): void
    {
        config(['app.url' => 'https://dash.lifepointeng.org']);

        // Every character is more modules in the grid, and more modules on a
        // fixed screen width means smaller squares to read from the back row.
        $this->assertSame('https://dash.lifepointeng.org/q/RPRF3', JoinQrCode::url($this->quiz()));
    }

    public function test_the_readable_url_drops_the_scheme(): void
    {
        config(['app.url' => 'https://dash.lifepointeng.org']);

        // Printed on screen for anyone typing it rather than scanning.
        $this->assertSame('dash.lifepointeng.org/q/RPRF3', JoinQrCode::readableUrl($this->quiz()));
    }

    public function test_a_trailing_slash_on_the_app_url_does_not_double_up(): void
    {
        config(['app.url' => 'https://dash.lifepointeng.org/']);

        $this->assertSame('https://dash.lifepointeng.org/q/RPRF3', JoinQrCode::url($this->quiz()));
    }

    public function test_the_code_renders_dark_on_light(): void
    {
        $svg = JoinQrCode::svg($this->quiz());

        /*
         * An inverted QR — light modules on a dark background — is refused
         * outright by a good number of phone cameras, and the screen behind
         * this one is nearly black.
         */
        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('fill="#ffffff"', strtolower($svg));
    }

    public function test_the_svg_scales_to_the_size_asked_for(): void
    {
        $this->assertStringContainsString('width="900"', JoinQrCode::svg($this->quiz(), 900));
        $this->assertStringContainsString('width="260"', JoinQrCode::svg($this->quiz(), 260));
    }
}
