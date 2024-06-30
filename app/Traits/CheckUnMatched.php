<?php

namespace App\Traits;
trait CheckUnMatched{
    public function isUnMatched($arr1, $arr2){
        $featuresLength= count($arr1);
        $thumbnailsLength= count($arr2);

        return $thumbnailsLength !==$featuresLength;
    }
}

?>
