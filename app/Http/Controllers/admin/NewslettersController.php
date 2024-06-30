<?php

namespace App\Http\Controllers\admin;

use App\Models\Newsletter;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class NewslettersController extends Controller
{
    use HttpResponses;

    public function getNewsletterSignups(){
        $newsletterSignups= Newsletter::filter()
        ->paginate(20);

        return $this->success($newsletterSignups);
    }   
}
