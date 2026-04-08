<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ImageUploads
{
    public static function storeResizedPublicImage(
        UploadedFile $file,
        string $directory,
        int $targetWidth,
        int $targetHeight
    ): string {
        $source = imagecreatefromstring($file->get());

        if (! $source) {
            throw new RuntimeException('Unable to process the uploaded image.');
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        $scale = max($targetWidth / $sourceWidth, $targetHeight / $sourceHeight);
        $scaledWidth = (int) ceil($sourceWidth * $scale);
        $scaledHeight = (int) ceil($sourceHeight * $scale);

        $resized = imagecreatetruecolor($targetWidth, $targetHeight);

        imagealphablending($resized, false);
        imagesavealpha($resized, true);

        $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
        imagefilledrectangle($resized, 0, 0, $targetWidth, $targetHeight, $transparent);

        imagecopyresampled(
            $resized,
            $source,
            (int) floor(($targetWidth - $scaledWidth) / 2),
            (int) floor(($targetHeight - $scaledHeight) / 2),
            0,
            0,
            $scaledWidth,
            $scaledHeight,
            $sourceWidth,
            $sourceHeight,
        );

        $extension = function_exists('imagewebp') ? 'webp' : 'png';
        $path = trim($directory, '/').'/'.Str::uuid().'.'.$extension;

        ob_start();

        if ($extension === 'webp') {
            imagewebp($resized, null, 85);
        } else {
            imagepng($resized);
        }

        $contents = ob_get_clean();

        imagedestroy($source);
        imagedestroy($resized);

        if ($contents === false) {
            throw new RuntimeException('Unable to encode the uploaded image.');
        }

        Storage::disk('public')->put($path, $contents);

        return $path;
    }
}
