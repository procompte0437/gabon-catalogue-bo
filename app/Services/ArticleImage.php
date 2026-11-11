<?php

namespace App\Services;

use App\Models\ArticleImage as StoredImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Photos d'articles : compressées en JPEG puis enregistrées dans la table article_images.
 * articles.image_path vaut « db/<key>.jpg ». Les anciens chemins « articles/… » (disque) restent lisibles.
 */
class ArticleImage
{
    public function store(UploadedFile $file): string
    {
        return $this->storeBytes($this->toJpeg($file));
    }

    /** Enregistre un JPEG déjà prêt ; la clé dépend du contenu, donc un même fichier n'est stocké qu'une fois. */
    public function storeBytes(string $jpeg, string $mime = 'image/jpeg'): string
    {
        $key = md5($jpeg);

        StoredImage::query()->firstOrCreate(
            ['key' => $key],
            ['mime' => $mime, 'size' => strlen($jpeg), 'data' => base64_encode($jpeg)],
        );

        return 'db/'.$key.'.jpg';
    }

    public function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (str_starts_with($path, 'db/')) {
            $key = static::keyFromPath($path);
            // Une même photo peut servir à plusieurs articles : on ne la supprime que si plus personne ne l'utilise.
            $stillUsed = \App\Models\Article::query()->where('image_path', $path)->count() > 1;
            if (! $stillUsed) {
                StoredImage::query()->where('key', $key)->delete();
            }

            return;
        }

        if (str_starts_with($path, 'articles/')) {
            Storage::disk('public')->delete($path);
        }
    }

    public static function keyFromPath(string $path): string
    {
        return basename(substr($path, 3), '.jpg');
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
