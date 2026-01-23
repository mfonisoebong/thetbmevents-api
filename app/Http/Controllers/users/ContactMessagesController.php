<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers;
use App\Http\Requests\StoreContactMessagesRequest;
use App\Models\ContactMessage;
use App\Traits\ApiResponses;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class ContactMessagesController extends Controller
{
    use HttpResponses, ApiResponses;
    public function store(StoreContactMessagesRequest $request){
        ContactMessage::create($request->except(['recaptcha_token']));
        return $this->success(null, 'Contact message saved');
    }
}
