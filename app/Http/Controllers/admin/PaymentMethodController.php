<?php

namespace App\Http\Controllers\admin;

use App\Http\Requests\UpdatePaystackPaymentMethodRequest;
use App\Http\Requests\UpdateFlwPaymentMethodRequest;
use App\Models\PaymentMethod;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PaymentMethodController extends Controller
{
    use HttpResponses;



    public function updateFlutterwavePaymentMethod(UpdateFlwPaymentMethodRequest $request){
        $request->validated($request->all());

        $flutterwave= PaymentMethod::where('gateway', 'flutterwave')
        ->first();

        if(!$flutterwave){
            $newVellaMethod= PaymentMethod::create([
                'gateway'=> 'flutterwave',
                'flutterwave_live_key'=> $request->live_key,
                'flutterwave_test_key'=> $request->test_key
            ]);
            return $this->success($newVellaMethod);
        }

        $flutterwave->update([
            'flutterwave_live_key'=> $request->live_key,
            'flutterwave_test_key'=> $request->test_key
        ]);



            return $this->success($flutterwave);

    }

    public function updatePaystackPaymentMethod(UpdatePaystackPaymentMethodRequest $request){
        $request->validated($request->all());
        $paystack= PaymentMethod::where('gateway', 'paystack')
        ->first();


        if(!$paystack){
            $newPaystackMethod= PaymentMethod::create([
                'gateway'=> 'paystack',
                'paystack_live_key'=> $request->live_key,
                'paystack_test_key'=> $request->test_key
            ]);
            return $this->success($newPaystackMethod);
        }

        $paystack->update([
            'paystack_live_key'=> $request->live_key,
            'paystack_test_key'=> $request->test_key
        ]);
        return $this->success($paystack);

    }
}
