<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ImageUploads
{
    public static function storeResized(
        UploadedFile $file,
        string $directory,
        int $targetWidth,
        int $targetHeight,
        string $mode = 'cover',
    ): string {
        $binary = $file->get();
        $source = @imagecreatefromstring($binary);

        if (! $source) {
            throw new RuntimeException('Unable to read uploaded image.');
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        if ($mode === 'cover') {
            $scale = max($targetWidth / $sourceWidth, $targetHeight / $sourceHeight);
            $resizedWidth = (int) ceil($sourceWidth * $scale);
            $resizedHeight = (int) ceil($sourceHeight * $scale);
            $srcX = (int) max(0, floor(($resizedWidth - $targetWidth) / 2 / $scale));
            $srcY = (int) max(0, floor(($resizedHeight - $targetHeight) / 2 / $scale));
            $srcCropWidth = (int) min($sourceWidth, ceil($targetWidth / $scale));
            $srcCropHeight = (int) min($sourceHeight, ceil($targetHeight / $scale));

            $canvas = self::blankCanvas($targetWidth, $targetHeight, $file->getMimeType() ?? '');

            imagecopyresampled(
                $canvas,
                $source,
                0,
                0,
                $srcX,
                $srcY,
                $targetWidth,
                $targetHeight,
                $srcCropWidth,
                $srcCropHeight,
            );
        } else {
            $scale = min($targetWidth / $sourceWidth, $targetHeight / $sourceHeight, 1);
            $resizedWidth = (int) max(1, round($sourceWidth * $scale));
            $resizedHeight = (int) max(1, round($sourceHeight * $scale));

            $canvas = self::blankCanvas($resizedWidth, $resizedHeight, $file->getMimeType() ?? '');

            imagecopyresampled(
                $canvas,
                $source,
                0,
                0,
                0,
                0,
                $resizedWidth,
                $resizedHeight,
                $sourceWidth,
                $sourceHeight,
            );
        }

        $extension = self::extensionForMimeType($file->getMimeType() ?? $file->getClientMimeType());
        $path = trim($directory, '/').'/'.Str::uuid().'.'.$extension;

        Storage::disk('public')->put($path, self::encodeImage($canvas, $extension));

        imagedestroy($source);
        imagedestroy($canvas);

        return $path;
    }

    protected static function blankCanvas(int $width, int $height, string $mimeType)
    {
        $canvas = imagecreatetruecolor($width, $height);

        if (in_array($mimeType, ['image/png', 'image/webp', 'image/gif'], true)) {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            imagefilledrectangle($canvas, 0, 0, $width, $height, $transparent);
        } else {
            $background = imagecolorallocate($canvas, 18, 18, 18);
            imagefilledrectangle($canvas, 0, 0, $width, $height, $background);
        }

        return $canvas;
    }

    protected static function encodeImage($image, string $extension): string
    {
        ob_start();

        match ($extension) {
            'png' => imagepng($image, null, 8),
            'webp' => imagewebp($image, null, 85),
            default => imagejpeg($image, null, 85),
        };

        return (string) ob_get_clean();
    }

    protected static function extensionForMimeType(?string $mimeType): string
    {
        return match ($mimeType) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };
    }
}
