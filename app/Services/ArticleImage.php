<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ArticleImage
{
    public function store(UploadedFile $file): string
    {
        $path = 'articles/'.Str::uuid().'.jpg';
        Storage::disk('public')->put($path, $this->toJpeg($file));

        return $path;
    }

    public function delete(?string $path): void
    {
        if (! $path || ! str_starts_with($path, 'articles/')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    private function toJpeg(UploadedFile $file): string
    {
        if (! extension_loaded('gd')) {
            $contents = file_get_contents($file->getRealPath());
            if ($contents === false) {
                throw new RuntimeException('Lecture de la photo impossible.');
            }

            return $contents;
        }

        $source = $file->getRealPath();
        $mime = (string) $file->getMimeType();
        $image = match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($source),
            'image/png' => @imagecreatefrompng($source),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($source) : false,
            'image/gif' => @imagecreatefromgif($source),
            default => false,
        };

        if (! $image instanceof \GdImage) {
            throw new RuntimeException('Ce format de photo n’est pas pris en charge.');
        }

        if ($mime === 'image/jpeg' || $mime === 'image/jpg') {
            $image = $this->orient($image, $source);
        }

        $image = $this->fit($image, 900);

        ob_start();
        imagejpeg($image, null, 75);
        imagedestroy($image);

        return (string) ob_get_clean();
    }

    private function orient(\GdImage $image, string $source): \GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($source);
        $orientation = (int) ($exif['Orientation'] ?? 1);
        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => null,
        };

        if ($rotated instanceof \GdImage) {
            imagedestroy($image);

            return $rotated;
        }

        return $image;
    }

    private function fit(\GdImage $image, int $max): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $targetWidth = $width;
        $targetHeight = $height;

        if ($width > $max || $height > $max) {
            if ($width >= $height) {
                $targetWidth = $max;
                $targetHeight = (int) round($height * $max / $width);
            } else {
                $targetHeight = $max;
                $targetWidth = (int) round($width * $max / $height);
            }
        }

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);
        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        imagedestroy($image);

        return $canvas;
    }
}
