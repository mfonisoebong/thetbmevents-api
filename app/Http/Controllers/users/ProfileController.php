<?php

namespace App\Http\Controllers\users;

use App\Http\Requests\UploadAvatarRequest;
use App\Models\Customer;
use App\Models\Sale;
use App\Traits\CurrentDateTime;
use App\Traits\HttpResponses;
use App\Traits\StoreImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    use HttpResponses, CurrentDateTime, StoreImage;

    private $default_commition = 0;


    public function getOverview(Request $request)
    {
        $user = $request->user();

        $events = count($user->events ?? []);
        // $ticketsSold= $user->sales->sum('tickets_bought');
        $tickets = $user->sales->sum('tickets_bought');


        $allTimeSales = $user->invoices()
            ->where('payment_status', 'success');
        $salesThisMonth = $user->invoices()
            ->where('payment_status', 'success')
            ->whereYear('created_at', $this->getCurrentYear())
            ->whereMonth('created_at', $this->getCurrentMonth());


        $allTimeSalesCount = $allTimeSales->count();
        $salesThisMonthCount = $salesThisMonth->count();

        $earningsPercent = !$allTimeSalesCount ? 0 : ($salesThisMonthCount / $allTimeSalesCount) * 100;


        $totalEarningsThisMonth = (float)$salesThisMonth->sum('amount');


        $earnings = [
            'amount' => $totalEarningsThisMonth,
            'sales' => number_format($earningsPercent, 1)
        ];

        $revenue = [];

        foreach ($this->months as $month) {
            $data = $this->calculateTotalProfitAndCommisions(
                $user->commision->rate ?? $this->default_commition,
                $user->id,
                $month
            );
            array_push($revenue, $data);
        }


        return $this->success([
            'events' => $events,
            'tickets_sold' => $tickets,
            'earnings' => $earnings,
            'commision_and_profit' => [
                ...$this->calculateTotalProfitAndCommisions(
                    $user->commision->rate ?? $this->default_commition,
                    $user->id,
                ),
                'rate' => $user->commision->rate ?? $this->default_commition
            ],
            'revenue' => $revenue
        ]);
    }

    public function getFinanceOverview(Request $request)
    {
        $user = $request->user();
        $commissionRate = $user->commision->rate ?? $this->default_commition;

        $ticketSales = $user->sales->sum('tickets_bought');
        $revenueAndProfit = $this->calculateSalesRevenueAndProfit(
            $commissionRate ?? $this->default_commition,
            $user->invoices()
                ->where('payment_status', 'success')
        );

        $userEvents = $user->events->toArray();
        usort($userEvents, function ($a, $b) {
            $aSales = Sale::filter()
                ->where('event_id', '=', $a['id'])
                ->get()
                ->toArray();
            $bSales = Sale::filter()
                ->where('event_id', '=', $b['id'])
                ->get()
                ->toArray();

            $aCount = count($aSales);
            $bCount = count($bSales);

            if ($aCount == $bCount) {
                return 0;
            }
            return ($aCount > $bCount) ? -1 : 1;
        });

        $eventsStats = array_map(function ($event) {
            $eventSales = Sale::filter()
                ->sum('tickets_bought');


            return [
                'id' => $event['id'],
                'title' => Str::of($event['title'])->limit(25) ?? $event['title'],
                'logo' => $event['logo'],
                'tickets' => $eventSales,
                'created_at' => $event['created_at'],
            ];
        }, $userEvents);

        $highestSellingEvents = array_slice($eventsStats, 0, 5);

        $allThroughTheYearStats = [];

        foreach ($this->months as $month) {
            $sales = Sale::where('organizer_id', '=', $user->id)
                ->whereYear('created_at', $this->getCurrentYear())
                ->whereMonth('created_at', $month)
                ->select('total')
                ->get()
                ->toArray();
            $data = $this->calculateTotalProfitAndRevenueStats(
                $commissionRate ?? $this->default_commition,
                $sales
            );

            array_push($allThroughTheYearStats, $data);
        }


        $topCustomers = Sale::filter()
            ->where('organizer_id', '=', $user->id)
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
                'total_tickets' => $customer['total_tickets'],
                'name' => $user->first_name . ' ' . $user->last_name
            ];
        }, $topCustomers);


        return $this->success([
            'tickets_sold' => $ticketSales,
            'commision_rate' => $commissionRate,
            'revenue_overview' => $revenueAndProfit,
            'highest_selling_events' => $highestSellingEvents,
            'overview' => $allThroughTheYearStats,
            'topCustomers' => $topCustomersList
        ]);
    }


    public function uploadAvatar(UploadAvatarRequest $request)
    {
        $user = $request->user();
        $userAvatar = $user->avatar;
        $basePath = 'storage/users/avatars/';
        $avatarFilepath = $basePath . Str::uuid()->toString() . '.webp';


        $this->storeImage($avatarFilepath, $userAvatar, $request->file('avatar'));
        $user->update([
            'avatar' => $avatarFilepath
        ]);

        return $this->success(null, 'Avatar updated successfull');
    }

    public function removeAvatar(Request $request)
    {
        $user = $request->user();

//        $completed = $this->removeFile($user->avatar_path);
//        if (!$completed) {
//            return $this->failed(500, null, 'An error occurred');
//        }
        $this->removeFile($user->avatar_path);
        $user->update([
            'avatar' => null
        ]);


        return $this->success(null, 'Avatar removed successfull');
    }

    private function calculateTotalProfitAndCommisions($rate, $user_id, $month = null)
    {

        $sales = $month ? Sale::where('organizer_id', '=', $user_id)
            ->whereMonth('created_at', $month)
            ->select('total')
            ->get()
            ->toArray() :
            Sale::where('organizer_id', '=', $user_id)
                ->select('total')
                ->get()
                ->toArray();

        $totalSalesArr = array_map(function ($sale) {
            return $sale['total'];
        }, $sales);

        $totalSales = array_reduce($totalSalesArr, function ($a, $b) {
            return $a + $b;
        }) ?? 0;


        $commision = ($rate / 100) * $totalSales;

        $profit = $totalSales - $commision;
        return [
            'commision' => (float)$commision,
            'profit' => (float)$profit
        ];
    }

    private function calculateTotalProfitAndRevenueStats($rate, $sales)
    {
        $totalSalesArr = array_map(function ($sale) {
            return $sale['total'];
        }, $sales);

        $totalSales = array_reduce($totalSalesArr, function ($a, $b) {
            return $a + $b;
        }) ?? 0;
        $commision = ($rate / 100) * $totalSales;

        $profit = $totalSales - $commision;
        return [
            'commision' => (float)$commision,
            'profit' => (float)$profit,
            'revenue' => (float)$totalSales
        ];
    }


    private function calculateSalesRevenueAndProfit($rate, $invoices)
    {
        $totalSales = $invoices->sum('amount');

        $commision = ($rate / 100) * $totalSales;

        $profit = $totalSales - $commision;
        return [
            'commision' => (float)$commision,
            'profit' => (float)$profit,
            'revenue' => (float)$totalSales
        ];
    }
}
