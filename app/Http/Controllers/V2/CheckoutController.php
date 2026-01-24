<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    use ApiResponses;

    public function applyCoupon(Request $request)
    {
        $validated = $request->validate([
            'coupon_code' => 'required|string',
            'event_id' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $code = trim((string)$validated['coupon_code']);
        $eventId = (string)$validated['event_id'];
        $amount = (float)$validated['amount'];

        $coupon = Coupon::query()
            ->where('code', $code)
            ->where('event_id', $eventId)
            ->first();

        if (!$coupon) {
            return $this->error('Invalid coupon code for this event', 422);
        }

        if ($coupon->status !== 'active') {
            return $this->error('Coupon is inactive', 422);
        }

        if (!$coupon->is_active) {
            return $this->error('Coupon is not active', 422);
        }

        // Limit: -1 = unlimited, otherwise must be > 0
        if ($coupon->limit === 0) {
            return $this->error('Coupon has been used up', 422);
        }

        $rawValue = $coupon->value;

        if ($coupon->type === 'percentage' && $rawValue > 100) {
            // Prevent negative totals when a bad value is stored.
            return $this->error('Coupon percentage value is invalid', 422);
        }

        $discount = (float)($coupon->calculateValue($amount) ?? 0);
        if ($discount < 0) {
            $discount = 0;
        }

        // Never discount more than the amount
        $discount = min($discount, $amount);

        $total = max(0, $amount - $discount);

        return $this->success([
            'coupon_id' => $coupon->id,
            'coupon_code' => $coupon->code,
            'type' => $coupon->type,
            'value' => $coupon->value,
            'amount' => $amount,
            'discount' => $discount,
            'total' => $total,
        ], 'Coupon applied');
    }
}
