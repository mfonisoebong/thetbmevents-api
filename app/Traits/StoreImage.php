<?php
namespace App\Traits;
use Illuminate\Support\Str;use Intervention\Image\Facades\Image;

trait StoreImage{

    protected $quality= 60;
    protected $encoding= 'webp';
    public function storeImage($path, $oldPath, $image){
        if($oldPath){
            $formatted= Str::isUrl($oldPath) ? str_replace(
                env('APP_URL').'/',
                '',
                $oldPath
            ): $oldPath;
            unlink(public_path($formatted));
        }

        $newPath= public_path($path);
        Image::make($image)
            ->encode('webp', 60)
            ->save($newPath);
    }
}

?>
