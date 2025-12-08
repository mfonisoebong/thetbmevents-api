<?php

namespace App\Http\Controllers\Mobile\Health;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
