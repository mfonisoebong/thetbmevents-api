<?php

namespace App\Http\Controllers\admin;

use App\Http\Resources\EventsWithTicketResource;
use App\Models\Commision;
use App\Models\Customer;
use App\Models\Event;
use App\Models\Transaction;
use App\Models\Sale;
use App\Models\User;
use App\Traits\ApiResponses;
use App\Traits\CurrentDateTime;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OverviewController extends Controller
{
    use CurrentDateTime, HttpResponses, ApiResponses;

    private $default_rate = 0;

    public function getOverview(Request $request)
    {


        $latestEvents = Event::latest('created_at')
            ->take(5)
            ->get();

        $allThroughTheYearStats = array_map(function ($month) {
            $sales = Transaction::whereYear('created_at', $this->getCurrentYear())
                ->whereMonth('created_at', $month)
                ->where('status', 'success');
            return $this->calculateNetRevenueAndCommision($sales);
        }, $this->months);
        $topCustomers = Sale::filter()
            ->select('customer_id', DB::raw('SUM(tickets_bought) as total_tickets'))
            ->groupBy('customer_id')
            ->orderByDesc('total_tickets')
            ->limit(10)
            ->get()
            ->toArray();

        $topEvents = Sale::filter()
            ->select('event_id', DB::raw('SUM(tickets_bought) as tickets_sold'))
            ->groupBy('event_id')
            ->orderByDesc('tickets_sold')
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


        $topOrganizersList = array_map(function ($event) {
            $organizerEvent = Event::where('id', $event['event_id'])
                ->first();


            return [
                'title' => $organizerEvent?->title,
                'organizer' => $organizerEvent?->user?->business_name,
                'tickets_sold' => $event['tickets_sold'],
                'id' => $organizerEvent?->user?->id
            ];

        }, $topEvents);

        return $this->success([
            'revenue' => $allThroughTheYearStats,
            'top_organizers' => $topOrganizersList,
            'top_customers' => $topCustomersList,
            'latest_events' => EventsWithTicketResource::collection($latestEvents),
        ]);

    }

    public function getNetRevenue(Request $request)
    {
        $netSales = Transaction::whereYear('created_at', $this->getCurrentYear())
            ->whereMonth('created_at', request('month'))
            ->where('status', 'success');

        $netRevenueCommisions = $this->calculateNetRevenueAndCommision($netSales);


        return $this->success($netRevenueCommisions);
    }

    public function getEventsOverview(Request $request)
    {
        $events = Event::whereYear('created_at', $this->getCurrentYear())
            ->whereMonth('created_at', request('month'))
            ->count();
        $users = User::count();
        $organizers = User::where('role', 'organizer')
            ->count();
        $staffs = User::where('role', 'admin')
            ->count();

        $netSales = Sale::whereYear('created_at', $this->getCurrentYear())
            ->whereMonth('created_at', request('month'))
            ->select('total', 'organizer_id', 'tickets_bought')
            ->get()
            ->toArray();

        $totalTicketsArr = array_map(function ($sale) {
            return $sale['tickets_bought'];
        }, $netSales);
        $totalTickets = array_reduce($totalTicketsArr, function ($a, $b) {
            return $a + $b;
        }) ?? 0;


        $eventOverview = [
            'tickets_sold' => $totalTickets,
            'events_created' => $events,
            'staffs' => $staffs,
            'users' => $users,
            'organizers' => $organizers
        ];


        return $this->success($eventOverview);
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
