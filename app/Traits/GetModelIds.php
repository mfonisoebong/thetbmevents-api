<?php

namespace App\Traits;

trait GetModelIds{

    public function getModelIds($model){
        $parsedModel= json_decode(json_encode($model));
        $ids= array_map(function ($m){
            return $m->id;
        }, $parsedModel);


        return $ids;

    }

}
