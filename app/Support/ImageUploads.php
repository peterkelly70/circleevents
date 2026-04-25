<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
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
        $bytes = $file->get();

        if (! is_string($bytes) || $bytes === '') {
            throw new RuntimeException('The uploaded image could not be read.');
        }

        self::guardImageUpload($bytes);

        $source = imagecreatefromstring($bytes);

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

    protected static function guardImageUpload(string $bytes): void
    {
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($bytes) ?: null;

        if (! in_array($mimeType, $allowedMimeTypes, true)) {
            throw new RuntimeException('Only JPEG, PNG, or WebP image uploads are allowed.');
        }

        $dimensions = getimagesizefromstring($bytes);

        if (! is_array($dimensions) || empty($dimensions[0]) || empty($dimensions[1])) {
            throw new RuntimeException('The uploaded file is not a valid image.');
        }

        $pixelCount = $dimensions[0] * $dimensions[1];

        if ($pixelCount > 40_000_000) {
            throw new RuntimeException('The uploaded image is too large to process safely.');
        }

        self::scanWithClamAv($bytes);
    }

    protected static function scanWithClamAv(string $bytes): void
    {
        $binary = Config::get('services.security.clamscan_binary');

        if (! is_string($binary) || trim($binary) === '') {
            return;
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'circleevents-upload-');

        if ($tmpFile === false) {
            throw new RuntimeException('Unable to prepare upload safety scan.');
        }

        file_put_contents($tmpFile, $bytes);

        $output = [];
        $exitCode = 0;

        exec(escapeshellcmd($binary).' --no-summary '.escapeshellarg($tmpFile), $output, $exitCode);

        @unlink($tmpFile);

        if ($exitCode === 1) {
            throw new RuntimeException('The uploaded file was rejected by the security scanner.');
        }

        if ($exitCode > 1) {
            throw new RuntimeException('The upload security scanner failed while checking this file.');
        }
    }
}
