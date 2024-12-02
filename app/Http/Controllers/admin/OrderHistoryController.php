<?php

namespace App\Http\Controllers\admin;

use App\Http\Requests\ExportOrderHistoryRequest;
use App\Http\Resources\OrderHistoryResource;
use App\Models\PurchasedTicket;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use League\Csv\Writer;

class OrderHistoryController extends Controller
{
    use HttpResponses;
    public function getOrderHistory()
    {
        $orderHistory = PurchasedTicket::latest()->paginate(20);
        $paginateMetaData = $orderHistory->toArray();
        $ordersResource = OrderHistoryResource::collection($orderHistory);
        $data = [...$paginateMetaData, 'data' => $ordersResource];
        return $this->success($data);
    }

    public function exportAsCSV(ExportOrderHistoryRequest $request)
    {

        $ids = explode(',', $request->ids);
        $orderHistory = PurchasedTicket::whereIn('id', $ids)
            ->get();;
        $csv = Writer::createFromString('');
        $csv->insertOne([
            'id',
            'Ticket No',
            'Event name',
            'Ticket name',
            'Ticket Price',
            'Organizer',
            'Created At',
        ]);
        $orders = OrderHistoryResource::collection($orderHistory)
            ->toArray($request);

        foreach ($orders as $order) {
            $csv->insertOne([
                $order['id'],
                $order['ticket_no'],
                $order['event_name'],
                $order['ticket_name'],
                $order['ticket_price'],
                $order['organizer'],
                $order['created_at'],
            ]);
        }

        $headers = [
            'Content-Type'  => 'text/csv',
            'Content-Disposition' => 'attachment; filename="orders.csv"',
        ];

        // Convert the CSV to a string
        $csvString = $csv->getContent();
        return response($csvString, 200, $headers);
    }
}
