<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\Invoice as InvoiceResource;
use App\Http\Resources\InvoiceDetailed as InvoiceDetailedResource;
use App\Http\Resources\Meal as MealResource;
use App\Invoice;
use App\Meal;
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



    public function invoiceMeal($invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);

        return new MealResource(Meal::where('id',$invoice->meal_id)->first());
    }

    public function allOrders()
    {
         $items = DB::table('invoices')
         ->join('meals', 'meals.id', '=', 'invoices.meal_id')
         ->join('users', 'meals.responsible_waiter_id', '=', 'users.id')
         ->select('invoices.*', 
         'meals.responsible_waiter_id AS responsible_waiter_id',
         'meals.table_number AS table_number',
         'users.name AS responsible_waiter_name')
         ->get();
         return response()->json(InvoiceDetailedResource::collection($items), 200);
    }


    public function orderDetails($id)
    {
        $item = DB::table('invoices')
        ->join('meals', 'meals.id', '=', 'invoices.meal_id')
        ->join('users', 'meals.responsible_waiter_id', '=', 'users.id')
        ->where('invoices.id', $id)
        ->select('invoices.*', 
        'meals.responsible_waiter_id AS responsible_waiter_id',
        'meals.table_number AS table_number',
        'users.name AS responsible_waiter_name')
        ->first();
        return new InvoiceDetailedResource($item);        
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
        return response()->json(InvoiceDetailedResource::collection($items), 200);
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
        return response()->json(InvoiceDetailedResource::collection($items), 200);

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
        $invoice = Invoice::findOrFail($id);
        
        $validatedData= $request->validate([
            'state' => 'in:pending,paid,not paid',
            'nif' => 'integer|digits:9',
            'name' => 'regex:/^[A-Za-záàâãéèêíóôõúçÁÀÂÃÉÈÍÓÔÕÚÇ ]+$/'
        ]);
        
        $invoice->fill($validatedData);
        $invoice->update();
        
        return new InvoiceResource($invoice);
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
