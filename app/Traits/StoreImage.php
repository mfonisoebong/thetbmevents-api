<?php
namespace App\Traits;

use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

trait StoreImage
{

    protected $quality = 60;
    protected $encoding = 'webp';

    public function storeImage($path, $oldPath, $image)
    {
        if ($oldPath) {
            $formatted = Str::isUrl($oldPath) ? str_replace(
                env('APP_URL') . '/',
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

    public function removeFile($path)
    {
        $formatted = Str::isUrl($path) ? str_replace(
            env('APP_URL') . '/',
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
