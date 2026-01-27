<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\CreateCouponRequest;
use App\Http\Resources\V2\CouponResource;
use App\Models\Coupon;
use Illuminate\Http\Request;

class OrganizerCouponController extends Controller
{

    public function index()
    {
        $myCoupons = Coupon::where('user_id', auth()->id())->get();
        return $this->success(CouponResource::collection($myCoupons));
    }

    public function createCoupon(CreateCouponRequest $request)
    {
        auth()->user()->coupons()->create($request->validated());
        return $this->success(null, 'Coupon created successfully');
    }

    public function updateCouponStatus(Request $request, Coupon $coupon)
    {
        $this->authorize('update', $coupon);

        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $coupon->status = $request->input('status');
        $coupon->save();

        return $this->success(null, 'Coupon status updated successfully');
    }

    public function deleteCoupon(Coupon $coupon)
    {
        $this->authorize('delete', $coupon);

        $coupon->delete();
        return $this->success(null, 'Coupon deleted successfully');
    }
}
