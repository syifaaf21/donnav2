<?php
namespace App\Services;

use setasign\Fpdi\Fpdi;

class PdfWatermarker
{
    /**
     * Stamp watermark text onto an existing PDF and return raw PDF content.
     * $watermark can contain placeholders already interpolated by caller.
     */
    public function stampText(string $sourcePath, string $watermarkText): string
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
     */
    public function stampImage(string $sourcePath, string $imagePath, float $widthMm = 30.0, float $marginMm = 10.0, int $opacityPercent = 40): string
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

            if ($srcImg) {
                $w = imagesx($srcImg);
                $h = imagesy($srcImg);
                // keep original image colors (do not alter brightness)
                $tmp = imagecreatetruecolor($w, $h);
                // preserve alpha
                imagealphablending($tmp, false);
                imagesavealpha($tmp, true);
                $transparent = imagecolorallocatealpha($tmp, 0, 0, 0, 127);
                imagefilledrectangle($tmp, 0, 0, $w, $h, $transparent);

                // imagecopymerge uses percentage for opacity; it does not preserve full alpha, but
                // this approach produces a translucent PNG adequate for watermarking.
                imagecopymerge($tmp, $srcImg, 0, 0, 0, 0, $w, $h, $opacity);
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
                // if GD load failed, fall back to original image
                if (file_exists($tmpFile)) @unlink($tmpFile);
                $tmpFile = null;
            }
        }

        for ($i = 1; $i <= $pageCount; $i++) {
            $tpl = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($tpl);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tpl);

            // compute image dimensions in user units (FPDF default unit = mm)
            $imgWmm = $widthMm;
            $imgHmm = max(1, $imgWmm * $aspect);

            // position bottom-right with margin
            $x = $size['width'] - $imgWmm - $marginMm;
            $y = $size['height'] - $imgHmm - $marginMm;

            // Add image with some transparency if supported (FPDF doesn't support alpha natively)
            // Here we just draw the image.
            $pdf->Image($useImage, $x, $y, $imgWmm, $imgHmm);
        }

        $out = $pdf->Output('S');

        // cleanup tmp
        if (!empty($tmpFile) && file_exists($tmpFile)) {
            @unlink($tmpFile);
        }

        return $out;
    }
}
