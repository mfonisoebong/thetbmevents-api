<?php

namespace App\Http\Controllers\users;

use App\Http\Requests\NewsletterRequest;
use App\Models\Newsletter;
use App\Traits\HttpResponses;

class NewsletterController extends Controller
{
    use HttpResponses;
    public function store(NewsletterRequest $request){
        $request->validated($request->all());

        Newsletter::create(['email'=> $request->email]);

        return $this->success(null, 'Thanks for subscribing to our newsletter');


    }
}
