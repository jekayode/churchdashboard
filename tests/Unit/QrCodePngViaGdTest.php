<?php

namespace Tests\Unit;

use App\Support\QrCodePngViaGd;
use PHPUnit\Framework\TestCase;

final class QrCodePngViaGdTest extends TestCase
{
    public function test_it_outputs_png_binary_with_expected_signature(): void
    {
        $png = QrCodePngViaGd::generate('https://example.com/event', 256, 2);

        $this->assertStringStartsWith("\x89PNG\r\n\x1a\n", $png);
        $this->assertGreaterThan(500, strlen($png));
    }

    public function test_it_rejects_empty_content(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        QrCodePngViaGd::generate('', 128, 2);
    }
}
