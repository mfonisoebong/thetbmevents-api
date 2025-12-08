<?php

namespace App\Http\Controllers\Mobile\Health;

use App\Http\Controllers\Controller;

class HealthController extends Controller
{
    public function __invoke()
    {
        return $this->success(
            ['status' => 'API is healthy'],
            'Health check successful'
        );
    }
}
