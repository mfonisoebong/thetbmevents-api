<?php
namespace App\Traits;

use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

trait StoreImage
{

    protected $quality = 60;
    protected $encoding = 'webp';

    public function storeImage($path, $oldPath, $image): void
    {
        if ($oldPath) {
            $formatted = Str::isUrl($oldPath) ? str_replace(
                config('app.url') . '/',
                '',
                $oldPath
            ) : $oldPath;
            try {
                unlink(public_path($formatted));
            } catch (\Throwable $e) {
            }
        }

        $newPath = public_path($path);
        Image::make($image)
            ->encode('webp', 60)
            ->save($newPath);
    }

    public function removeFile($path): void
    {
        $formatted = Str::isUrl($path) ? str_replace(
            config('app.url') . '/',
            '',
            $path
        ) : $path;
        try {
            unlink(public_path($formatted));
        } catch (\Throwable $e) {
        }
    }
}

?>
