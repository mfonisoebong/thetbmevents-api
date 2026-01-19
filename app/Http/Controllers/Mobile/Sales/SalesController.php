<?php

namespace App\Http\Controllers\Mobile\Sales;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\Sale\EventSaleResource;
use App\Http\Resources\SalesResource;
use App\Models\Event;
use App\Models\Sale;
use App\Traits\HttpResponses;
use App\Traits\Pagination;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    use HttpResponses, Pagination;
    public function getSales(Request $request)
    {
        $user = $request->user();

        $sales = Sale::where('organizer_id', $user->id)
            ->paginate(20);


        $formattedSalesResource = SalesResource::collection($sales);

        $formattedSales = $this->paginatedData($sales, $formattedSalesResource);
        return $this->success($formattedSales);
    }

    public function resendPurchasedTickets(Sale $sale)
    {

        $invoice = $sale->invoice;

        $invoice->sendInvoice();

        return $this->success(null, 'Transaction has been resent');
    }

    public function overview(Request $request)
    {
        $events = $request->user()->events()->paginate(12);
        $list = EventSaleResource::collection($events);
        $data = $this->paginatedData($events, $list);

        return $this->success($data);
    }

    public function getEventSales(Request $request, Event $event)
    {
        $sales = $event->sales()->paginate(20);

        $formattedSalesResource = SalesResource::collection($sales);

        $formattedSales = $this->paginatedData($sales, $formattedSalesResource);
        return $this->success($formattedSales);
    }
}
