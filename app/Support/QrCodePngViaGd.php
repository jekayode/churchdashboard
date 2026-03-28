<?php

declare(strict_types=1);

namespace App\Support;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;

/**
 * Renders a QR code as a PNG using GD, without the imagick extension.
 *
 * Matches simple-qrcode defaults used elsewhere: EC level L, default byte encoding (ISO-8859-1),
 * quiet zone expressed as module count (same meaning as QrCode::margin()).
 */
final class QrCodePngViaGd
{
    public static function generate(string $content, int $sizePixels, int $marginModules = 2): string
    {
        if ($content === '') {
            throw new \InvalidArgumentException('QR content cannot be empty.');
        }

        if (! function_exists('imagecreatetruecolor')) {
            throw new \RuntimeException('The GD extension is required to generate PNG QR codes.');
        }

        $qrCode = Encoder::encode(
            $content,
            ErrorCorrectionLevel::L(),
            Encoder::DEFAULT_BYTE_MODE_ECODING
        );

        $matrix = $qrCode->getMatrix();
        $matrixSize = $matrix->getWidth();

        if ($matrixSize !== $matrix->getHeight()) {
            throw new \RuntimeException('QR matrix must be square.');
        }

        $totalModules = $matrixSize + ($marginModules * 2);
        if ($totalModules <= 0) {
            throw new \RuntimeException('Invalid QR matrix dimensions.');
        }

        $moduleSize = $sizePixels / $totalModules;

        $im = imagecreatetruecolor($sizePixels, $sizePixels);
        if ($im === false) {
            throw new \RuntimeException('Could not create GD image.');
        }

        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 0, 0, 0);
        imagefill($im, 0, 0, $white);

        for ($my = 0; $my < $matrixSize; $my++) {
            for ($mx = 0; $mx < $matrixSize; $mx++) {
                if ($matrix->get($mx, $my) === 0) {
                    continue;
                }

                $x0 = (int) floor(($mx + $marginModules) * $moduleSize);
                $y0 = (int) floor(($my + $marginModules) * $moduleSize);
                $x1 = (int) floor(($mx + $marginModules + 1) * $moduleSize);
                $y1 = (int) floor(($my + $marginModules + 1) * $moduleSize);

                $x1 = min($x1, $sizePixels);
                $y1 = min($y1, $sizePixels);

                if ($x0 >= $sizePixels || $y0 >= $sizePixels || $x1 <= $x0 || $y1 <= $y0) {
                    continue;
                }

                imagefilledrectangle($im, $x0, $y0, $x1 - 1, $y1 - 1, $black);
            }
        }

        ob_start();
        $ok = imagepng($im);
        $binary = ob_get_clean();
        imagedestroy($im);

        if ($ok === false || $binary === false || $binary === '') {
            throw new \RuntimeException('PNG output failed.');
        }

        return $binary;
    }
}
