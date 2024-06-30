<?php

namespace App\Http\Controllers\admin;

use App\Http\Requests\UpdatePaystackPaymentMethodRequest;
use App\Http\Requests\UpdateVellaPaymentMethodRequest;
use App\Models\PaymentMethod;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PaymentMethodController extends Controller
{
    use HttpResponses;



    public function updateVellaPaymentMethod(UpdateVellaPaymentMethodRequest $request){
        $request->validated($request->all());

        $vella= PaymentMethod::where('gateway', 'vella')
        ->first();

        if(!$vella){
            $newVellaMethod= PaymentMethod::create([
                'gateway'=> 'vella',
                'vella_tag'=> $request->vella_tag,
                'vella_webhook_url'=> $request->webhook_url,
                'vella_live_key'=> $request->live_key,
                'vella_test_key'=> $request->test_key
            ]);
            return $this->success($newVellaMethod);
        }

        $vella->update([
            'vella_tag'=> $request->vella_tag,
            'vella_webhook_url'=> $request->webhook_url,
            'vella_live_key'=> $request->live_key,
            'vella_test_key'=> $request->test_key
        ]);



            return $this->success($vella);

    }

    public function updatePaystackPaymentMethod(UpdatePaystackPaymentMethodRequest $request){
        $request->validated($request->all());
        $paystack= PaymentMethod::where('gateway', 'paystack')
        ->first();


        if(!$paystack){
            $newPaystackMethod= PaymentMethod::create([
                'gateway'=> 'paystack',
                'paystack_webhook_url'=> $request->webhook_url,
                'paystack_live_key'=> $request->live_key,
                'paystack_test_key'=> $request->test_key
            ]);
            return $this->success($newPaystackMethod);
        }

        $paystack->update([
            'paystack_webhook_url'=> $request->webhook_url,
            'paystack_live_key'=> $request->live_key,
            'paystack_test_key'=> $request->test_key
        ]);
        return $this->success($paystack);

    }
}
