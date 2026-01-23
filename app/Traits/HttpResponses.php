<?php

namespace App\Traits;

trait HttpResponses {
    public function failed($code, $data = null, $message = null) {
        return response()->json([
            'data'=> $data,
            'message'=> $message,
            'status'=> 'failed'
        ], $code);
    }
}
