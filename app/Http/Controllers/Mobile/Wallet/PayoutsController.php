<?php

namespace App\Http\Controllers\Mobile\Wallet;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\Wallet\PayoutResource;
use App\Mail\PayoutUpdatedMail;
use App\Models\Payout;
use App\Traits\Pagination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

class PayoutsController extends Controller
{
    use Pagination;

    public function store(Request $request)
    {
        try {
            $authorized = Gate::allows('create', Payout::class);

            if (!$authorized) return $this->failed(403, null, 'Unauthorized');

            $data = $request->validate([
                'amount' => ['required', 'numeric', 'min:100'],
                'organizer_bank_details_id' => ['required', 'exists:organizer_bank_details,id'],
            ]);
            $user = $request->user();
            $payout = $user->payouts()->create($data);
            $payout->refresh();

            Mail::to($user->email)->send(new PayoutUpdatedMail($payout));

            return $this->success(null, 'Payout created successfully');
        } catch (\Exception $e) {
            return $this->failed(500, $e->getTrace(), $e->getMessage());
        }

    }

    public function viewAll(Request $request)
    {
        $user = $request->user();
        $payouts = $user->payouts()->filter()->paginate(10);
        $list = PayoutResource::collection($payouts);
        $data = $this->paginatedData($payouts, $list);

        return $this->success($data);
    }

    public function viewWalletInfo(Request $request)
    {
        $user = $request->user();
        $data = [
            'available_balance' => currencyFormatter($user->wallet->balance),
            'pending_payouts' => currencyFormatter($user->payouts()->where('status', 'pending')->sum('amount')),
        ];

        return $this->success($data);
    }
}
