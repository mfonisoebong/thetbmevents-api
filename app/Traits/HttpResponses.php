<?php

namespace App\Traits;

trait HttpResponses{
    public function success($data=null, $message=null,$code=200){
        return response()->json([
            'data'=> $data,
            'message'=> $message,
            'status'=> 'success'
        ], $code);
    }
    public function failed($code, $data=null, $message=null){
        return response()->json([
            'data'=> $data,
            'message'=> $message,
            'status'=> 'failed'
        ], $code);
    }
}