<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Invoice;
use App\Http\Resources\InvoiceItem as InvoiceItemResource;
use App\Http\Resources\InvoiceItemDetailed as InvoiceItemDetailedResource;
use Illuminate\Support\Facades\DB;
use App\InvoiceItem;
use App\Item;
use App\Meal;
use App\Order;

class InvoiceItemsControllerAPI extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function itemsFromAnInvoice($id){
        $invoice = Invoice::findOrFail($id);

        $items= DB::table('invoice_items')
        ->join('items', 'items.id', '=', 'invoice_items.item_id')
        ->where('invoice_id', $id)
        ->select('invoice_items.*', 
        'items.name AS item_name')
        ->get();

        return InvoiceItemDetailedResource::collection($items); 
    }
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($invoiceId, $mealId)
    {
        $meal = Meal::findOrFail($mealId);
        $orders = Order::where('meal_id', $mealId)->where('state', 'delivered')->get();
        $created = [];
        
        foreach($orders as $order) {
            if(!in_array($order->item_id, $created)) {
                $item = Item::withTrashed()->where('id', $order->item_id)->first();

                $quantity = 0;
                foreach ($orders as $ord) {
                    if ($ord->item_id == $order->item_id) {
                        $quantity++;
                    }
                }

                $invoiceItem = new InvoiceItem();
                $invoiceItem->invoice_id = $invoiceId;
                $invoiceItem->item_id = $order->item_id;
                $invoiceItem->quantity = $quantity;
                $invoiceItem->unit_price = $item->price;
                $invoiceItem->sub_total_price = $quantity * $item->price;

                $invoiceItem->save();
                array_push($created, $invoiceItem->item_id);
            }
        }

        $result = InvoiceItem::where('invoice_id', $invoiceId)->get();
        return response()->json(InvoiceItemResource::collection($result), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
