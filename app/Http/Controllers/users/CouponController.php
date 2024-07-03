<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers;
use App\Http\Requests\StoreCouponRequest;
use App\Http\Resources\CouponListItemResource;
use App\Models\Coupon;
use App\Models\Event;
use App\Traits\HttpResponses;
use App\Traits\Pagination;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    use HttpResponses, Pagination;

    public function viewAll(Request $request)
    {
        $user = $request->user();

        if($user->role === 'admin'){
            $coupons = Coupon::paginate(12);

        } else{

            $coupons = Coupon::where('user_id', $user->id)->paginate(12);

        }

        $couponList = CouponListItemResource::collection($coupons);

        $metadata = $coupons->toArray();


        $data = [...$metadata, 'data' => $couponList];

        return $this->success($data);
    }

    public function view(Coupon $coupon)
    {

        $this->authorize('view', $coupon);
        $data = new CouponListItemResource($coupon);
        return $this->success($data);

    }

    public function store(StoreCouponRequest $request)
    {
        $this->authorize('create', Coupon::class);
        $user = $request->user();

        $coupon = $user->coupons()->create($request->validated());
        $data = new CouponListItemResource($coupon);
        return $this->success($data);
    }

    public function update(Coupon $coupon, StoreCouponRequest $request)
    {
        $coupon->update($request->validated());
        $data = new CouponListItemResource($coupon);
        return $this->success($data);
    }

    public function destroy(Coupon $coupon)
    {
        $this->authorize('delete', $coupon);
        $coupon->delete();

        return $this->success();

    }
}
