<?php

namespace App\Http\Controllers\admin;

use App\Events\RevenueOverview;
use App\Models\Commision;
use App\Models\Customer;
use App\Models\Event;
use App\Models\Transaction;
use App\Models\RevenueCommisionSnapshot;
use App\Models\Sale;
use App\Traits\ApiResponses;
use App\Traits\CurrentDateTime;
use App\Traits\GetTopOrganizers;
use App\Traits\HttpResponses;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinancesController extends Controller
{
    use CurrentDateTime, HttpResponses, ApiResponses, GetTopOrganizers;

    public $default_rate = 0;

    public function getRevenueOverview()
    {
        $revenueSnapshot = RevenueCommisionSnapshot::first();
        $netSales = Transaction::where('status', 'success');

        $netRevenueCommisions = $this->calculateNetRevenueAndCommision($netSales);


        $revenueDataSnap = $revenueSnapshot?->net_revenue ?? 0;
        $commsiionDataSnap = $revenueSnapshot?->net_commision ?? 0;

        $revenueDifference = ($netRevenueCommisions['net_revenue'] - $revenueDataSnap);
        $comissionDifference = ($netRevenueCommisions['net_commision'] - $commsiionDataSnap);

        $revenueRate =
            $netRevenueCommisions['net_revenue'] ?
                ($revenueDifference / $netRevenueCommisions['net_revenue']) * 100
                : 0;
        $comissionRate =
            $netRevenueCommisions['net_commision'] ?
                ($comissionDifference / $netRevenueCommisions['net_commision']) * 100
                : 0;


        event(new RevenueOverview($netRevenueCommisions));


        return $this->success([
            ...$netRevenueCommisions,
            'revenue_rate' => $revenueRate,
            'commision_rate' => $comissionRate,
        ]);
    }

    public function getFinancesAllThroughTheYear()
    {

        $allThroughTheYearStats = array_map(function ($month) {
            $sales = Transaction::whereYear('created_at', $this->getCurrentYear())
                ->whereMonth('created_at', $month);

            return $this->calculateNetRevenueAndCommision($sales);
        }, $this->months);

        return $this->success($allThroughTheYearStats);

    }

    public function getTopOrganizers()
    {
        return $this->success($this->computeTopOrganizers());
    }

    public function getTopCustomers()
    {
        $topCustomers = Sale::filter()
            ->select('customer_id', DB::raw('SUM(tickets_bought) as total_tickets'))
            ->groupBy('customer_id')
            ->orderByDesc('total_tickets')
            ->limit(10)
            ->get()
            ->toArray();
        $topCustomersList = array_map(function ($customer) {
            $user = Customer::where('id', '=', $customer['customer_id'])
                ->first();
            return [
                'id' => $user->id,
                'email' => Str::of($user->email)->limit(25),
                'avatar' => null,
                'total_tickets' => $customer['total_tickets'],
                'name' => Str::of($user->full_name)->limit(15)
            ];
        }, $topCustomers);
        return $this->success($topCustomersList);


    }

    private function calculateNetRevenueAndCommision($invoices)
    {
        $sales = $invoices->get();

        $totalCommisionsArr = array_map(function ($sale) {
            $commision = Commision::where('user_id', $sale['organizer_id'])
                ->select('rate')
                ->first();
            $rate = $commision ? $commision->rate : $this->default_rate;
            return $sale['amount'] * $rate / 100;
        }, $sales->toArray());


        $totalCommisions = array_reduce($totalCommisionsArr, function ($a, $b) {
            return $a + $b;
        }) ?? 0;

        return [
            'net_revenue' => $invoices->sum('amount'),
            'net_commision' => $totalCommisions
        ];
    }

}
