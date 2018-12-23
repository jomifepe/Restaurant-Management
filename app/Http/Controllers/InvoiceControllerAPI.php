<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\Invoice as InvoiceResource;
use App\Http\Resources\InvoicePending as InvoicePendingResource;
use App\Invoice;
use Illuminate\Support\Facades\DB;

class InvoiceControllerAPI extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return InvoiceResource::collection(Invoice::all());
    }

    public function pendingOrders()
    {
        //dd("entrou");
        $items = DB::table('invoices')
        ->join('meals', 'meals.id', '=', 'invoices.meal_id')
        ->join('users', 'meals.responsible_waiter_id', '=', 'users.id')
        ->where('invoices.state', 'pending')
        ->select('invoices.*',
        'meals.responsible_waiter_id AS responsible_waiter_id',
        'meals.table_number AS table_number',
        'users.name AS responsible_waiter_name')
        ->get();
        return response()->json(InvoicePendingResource::collection($items), 200);
        //return InvoiceResource::collection(Invoice::where('state','pending')->get());
    }
    public function paidOrders()
    {
        $items = DB::table('invoices')
        ->join('meals', 'meals.id', '=', 'invoices.meal_id')
        ->join('users', 'meals.responsible_waiter_id', '=', 'users.id')
        ->where('invoices.state', 'paid')
        ->select('invoices.*',
        'meals.responsible_waiter_id AS responsible_waiter_id',
        'meals.table_number AS table_number',
        'users.name AS responsible_waiter_name')
        ->get();
        return response()->json(InvoicePendingResource::collection($items), 200);

       // return InvoiceResource::collection(Invoice::where('state','paid')->get());
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
