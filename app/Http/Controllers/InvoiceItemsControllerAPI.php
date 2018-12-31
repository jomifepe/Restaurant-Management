<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Invoice;
use App\Http\Resources\InvoiceItems as InvoiceItemsResource;
use Illuminate\Support\Facades\DB;
use App\InvoiceItem;
use App\Item;

class InvoiceItemsControllerAPI extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function itemsFromAnInvoice($invoiceId){
        $invoice = Invoice::findOrFail($invoiceId);

        $items= DB::table('invoice_items')
        ->join('items', 'items.id', '=', 'invoice_items.item_id')
        ->where('invoice_id', $invoiceId)
        ->select('invoice_items.*', 
        'items.name AS item_name')
        ->get();

        return response()->json(InvoiceItemsResource::collection($items), 200); 
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
    public function store(Request $request)
    {
        // $data = $request->validate([
        //     'invoice_id' => 'required|integer|exists:invoices,id',
        //     'item_id' => 'required|integer|exists:items,id',
        //     'quantity' => 'required|integer',
        //     'unit_price' => 'required|numeric',
        //     'sub_total_price' => 'required|numeric'
        // ]);

        $invoiceItem = new InvoiceItem();
        $invoiceItem->fill($request->all());
        $invoiceItem->save();

        
        $item = Item::findOrFail($invoiceItem->item_id);
        $invoiceItem->item_name = $item->name;

        return response()->json(new InvoiceItemsResource($invoiceItem), 201);
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
