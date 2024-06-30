<?php

namespace App\Http\Controllers\users;

use App\Models\PaymentMethod;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    use HttpResponses;
    public function getPaymentMethod(Request $request){
        $gateway= $request->gateway;
        $paymentMethod= PaymentMethod::where('gateway', $gateway)
            ->first();


        return $this->success($paymentMethod);
    }

}
