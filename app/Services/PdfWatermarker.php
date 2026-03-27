<?php
namespace App\Services;

use setasign\Fpdi\Fpdi;
use Throwable;

class PdfWatermarker
{
    /**
     * Stamp watermark text onto an existing PDF and return raw PDF content.
     * $watermark can contain placeholders already interpolated by caller.
     */
    public function stampText(string $sourcePath, string $watermarkText): string
    {
        try {
            return $this->stampTextWithFpdi($sourcePath, $watermarkText);
        } catch (Throwable $e) {
            if (!$this->isLikelyFpdiCompressionError($e)) {
                throw $e;
            }

            $normalized = $this->normalizePdfWithGhostscript($sourcePath);
            if ($normalized === null) {
                throw $e;
            }

            try {
                return $this->stampTextWithFpdi($normalized, $watermarkText);
            } finally {
                @unlink($normalized);
            }
        }
    }

    private function stampTextWithFpdi(string $sourcePath, string $watermarkText): string
    {
        $pdf = new Fpdi();

        $pageCount = $pdf->setSourceFile($sourcePath);
        for ($i = 1; $i <= $pageCount; $i++) {
            $tpl = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($tpl);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tpl);

            // watermark styling: use a larger centered, lighter, non-rotated text
            // Choose font size relative to page dimensions (make noticeably larger)
            $baseSize = min($size['width'], $size['height']);
            // increase size: use a larger ratio so CONFIDENTIAL appears bigger on page
            $fontSize = max(22, (int)($baseSize / 8));
            // use bold weight and a very light gray color
            $pdf->SetFont('Helvetica', 'B', $fontSize);
            $pdf->SetTextColor(230, 230, 230);

            // place centered horizontally and vertically
            $pdf->SetXY(0, ($size['height'] / 2) - ($fontSize / 2));
            $pdf->Cell(0, $fontSize, $watermarkText, 0, 0, 'C');
        }

        return $pdf->Output('S');
    }

    /**
     * Stamp an image (PNG/JPG) onto each page of the source PDF at bottom-right.
     * $imagePath must be a filesystem path to the image.
     *
     * If $renderBehindContent is true, watermark is painted first and page content
     * is drawn on top, making watermark appear as background.
     */
    public function stampImage(
        string $sourcePath,
        string $imagePath,
        float $widthMm = 30.0,
        float $marginMm = 10.0,
        int $opacityPercent = 40,
        bool $renderBehindContent = false
    ): string
    {
        try {
            return $this->stampImageWithFpdi($sourcePath, $imagePath, $widthMm, $marginMm, $opacityPercent, $renderBehindContent);
        } catch (Throwable $e) {
            if (!$this->isLikelyFpdiCompressionError($e)) {
                throw $e;
            }

            $normalized = $this->normalizePdfWithGhostscript($sourcePath);
            if ($normalized === null) {
                throw $e;
            }

            try {
                return $this->stampImageWithFpdi($normalized, $imagePath, $widthMm, $marginMm, $opacityPercent, $renderBehindContent);
            } finally {
                @unlink($normalized);
            }
        }
    }

    private function stampImageWithFpdi(
        string $sourcePath,
        string $imagePath,
        float $widthMm = 30.0,
        float $marginMm = 10.0,
        int $opacityPercent = 40,
        bool $renderBehindContent = false
    ): string
    {
        $pdf = new Fpdi();

        $pageCount = $pdf->setSourceFile($sourcePath);

        // try to get image native aspect ratio
        $imgSize = @getimagesize($imagePath);
        $imgW = $imgSize[0] ?? 0;
        $imgH = $imgSize[1] ?? 0;
        $aspect = ($imgW > 0 && $imgH > 0) ? ($imgH / $imgW) : 1;

        // If opacity < 100, create a temporary translucent PNG via GD
        $useImage = $imagePath;
        $tmpFile = null;
        if ($opacityPercent < 100) {
            $opacity = max(0, min(100, $opacityPercent));
            $tmpFile = tempnam(sys_get_temp_dir(), 'wm_') . '.png';
            // load source
            $srcImg = null;
            $mime = $imgSize['mime'] ?? '';

            if (stripos($mime, 'png') !== false) {
                $srcImg = @imagecreatefrompng($imagePath);
            } elseif (stripos($mime, 'jpeg') !== false || stripos($mime, 'jpg') !== false) {
                $srcImg = @imagecreatefromjpeg($imagePath);
            } elseif (stripos($mime, 'gif') !== false) {
                $srcImg = @imagecreatefromgif($imagePath);
            }

            if ($srcImg && $opacity < 100 && $tmpFile) {
                $w = imagesx($srcImg);
                $h = imagesy($srcImg);
                $tmp = imagecreatetruecolor($w, $h);
                imagealphablending($tmp, false);
                imagesavealpha($tmp, true);
                $transparent = imagecolorallocatealpha($tmp, 0, 0, 0, 127);
                imagefilledrectangle($tmp, 0, 0, $w, $h, $transparent);

                if (stripos($mime, 'png') !== false) {
                    // Preserve existing PNG transparency and scale alpha by requested opacity.
                    for ($x = 0; $x < $w; $x++) {
                        for ($y = 0; $y < $h; $y++) {
                            $rgba = imagecolorat($srcImg, $x, $y);
                            $a = ($rgba >> 24) & 0x7F;
                            $r = ($rgba >> 16) & 0xFF;
                            $g = ($rgba >> 8) & 0xFF;
                            $b = $rgba & 0xFF;

                            // Convert desired opacity to additional transparency.
                            $newAlpha = 127 - (int) round((127 - $a) * ($opacity / 100));
                            $newAlpha = max(0, min(127, $newAlpha));

                            $color = imagecolorallocatealpha($tmp, $r, $g, $b, $newAlpha);
                            imagesetpixel($tmp, $x, $y, $color);
                        }
                    }
                } else {
                    // JPEG/GIF fallback.
                    imagecopymerge($tmp, $srcImg, 0, 0, 0, 0, $w, $h, $opacity);
                }

                imagepng($tmp, $tmpFile);
                imagedestroy($tmp);
                imagedestroy($srcImg);
                $useImage = $tmpFile;
                // refresh imgSize
                $imgSize = @getimagesize($useImage);
                $imgW = $imgSize[0] ?? $imgW;
                $imgH = $imgSize[1] ?? $imgH;
                $aspect = ($imgW > 0 && $imgH > 0) ? ($imgH / $imgW) : $aspect;
            } else {
                if ($srcImg) {
                    imagedestroy($srcImg);
                }
                // if GD load failed, fall back to original image
                if (file_exists($tmpFile)) @unlink($tmpFile);
                $tmpFile = null;
            }
        }

        for ($i = 1; $i <= $pageCount; $i++) {
            $tpl = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($tpl);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);

            // compute image dimensions in user units (FPDF default unit = mm)
            $imgWmm = $widthMm;
            $imgHmm = max(1, $imgWmm * $aspect);

            // position bottom-right with margin
            $x = $size['width'] - $imgWmm - $marginMm;
            $y = $size['height'] - $imgHmm - $marginMm;

            if ($renderBehindContent) {
                // Background watermark: draw image first, then page content on top.
                $pdf->Image($useImage, $x, $y, $imgWmm, $imgHmm);
                $pdf->useTemplate($tpl);
            } else {
                $pdf->useTemplate($tpl);
                $pdf->Image($useImage, $x, $y, $imgWmm, $imgHmm);
            }
        }

        $out = $pdf->Output('S');

        // cleanup tmp
        if (!empty($tmpFile) && file_exists($tmpFile)) {
            @unlink($tmpFile);
        }

        return $out;
    }

    private function isLikelyFpdiCompressionError(Throwable $e): bool
    {
        $message = strtolower($e->getMessage());

        $hasUnsupportedKeyword = str_contains($message, 'unsupported')
            || str_contains($message, 'not supported')
            || str_contains($message, 'fpdi-pdf-parser');

        $hasCompressionHint = str_contains($message, 'compression')
            || str_contains($message, 'filter')
            || str_contains($message, 'parser');

        return $hasUnsupportedKeyword && $hasCompressionHint;
    }

    private function normalizePdfWithGhostscript(string $sourcePath): ?string
    {
        $outputPath = tempnam(sys_get_temp_dir(), 'gs_norm_') . '.pdf';
        $escapedOutput = escapeshellarg($outputPath);
        $escapedInput = escapeshellarg($sourcePath);

        foreach ($this->ghostscriptCandidates() as $binary) {
            $escapedBinary = escapeshellarg($binary);
            $command = $escapedBinary
                . ' -q -dSAFER -dBATCH -dNOPAUSE -sDEVICE=pdfwrite -dCompatibilityLevel=1.4'
                . ' -dDetectDuplicateImages=true -dCompressFonts=true'
                . ' -sOutputFile=' . $escapedOutput
                . ' ' . $escapedInput
                . ' 2>&1';

            $out = [];
            $exitCode = 1;
            @exec($command, $out, $exitCode);

            if ($exitCode === 0 && is_file($outputPath) && filesize($outputPath) > 0) {
                return $outputPath;
            }
        }

        if (is_file($outputPath)) {
            @unlink($outputPath);
        }

        return null;
    }

    private function ghostscriptCandidates(): array
    {
        $configured = trim((string) env('GS_BIN', ''));
        $candidates = [];

        if ($configured !== '') {
            $candidates[] = $configured;
        }

        if (DIRECTORY_SEPARATOR === '\\') {
            $candidates[] = 'gswin64c';
            $candidates[] = 'gswin32c';
        }

        $candidates[] = 'gs';

        return array_values(array_unique($candidates));
    }
}
