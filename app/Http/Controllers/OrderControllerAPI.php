<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Order as OrderResource;
use App\Http\Resources\OrderMeal as OrderMealResource;
use App\Http\Resources\Item as ItemResource;
use App\Http\Resources\ItemOrder as ItemOrderResource;
use App\Order;
use App\Meal;
use App\Item;
use App\User;
use Validator;

class OrderControllerAPI extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return OrderResource::collection(Order::all());
    }

    public function toPrepare($id)
    {
        User::findOrFail($id);
        
        $myinPrepOrders = Order::select('orders.*', 
            'meals.table_number AS meal_table_number',
            'items.name AS item_name', 'items.photo_url AS item_photo',
            'items.type AS item_type')
            ->join('meals', 'meals.id', '=', 'orders.meal_id')
            ->join('items', 'items.id', '=', 'orders.item_id')            
            ->where('orders.responsible_cook_id', $id)
            ->where('orders.state', 'in preparation')
            ->orderBy('created_at','asc')
            ->get();
        
        $confirmedOrders = Order::select('orders.*', 
            'meals.table_number AS meal_table_number',
            'items.name AS item_name', 'items.photo_url AS item_photo',
            'items.type AS item_type')
            ->join('meals', 'meals.id', '=', 'orders.meal_id')
            ->join('items', 'items.id', '=', 'orders.item_id')            
            ->where('orders.state', 'confirmed')
            ->orderBy('created_at','asc')
            ->get();
            
        return OrderMealResource::collection($myinPrepOrders->merge($confirmedOrders));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'state' => 'required|in:pending',
            'item_id' => 'required|integer|exists:items,id',
            'meal_id' => 'required|integer|exists:meals,id',
            'start' => 'required|date'
        ]);

        $meal = Meal::findOrFail($request->meal_id);
        $item = Item::findOrFail($request->item_id);
        $meal->total_price_preview += $item->price;
        $meal->save();

        $order = new Order();
        $order->fill($request->all());
        $order->save();
        return response()->json(new OrderResource($order), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return OrderResource(Order::find($id));
    }

    public function mealOrders($mealId) {
        return Order::where('meal_id', $mealId)->get();
    }

    public function mealItems($mealId)
    {
        $items = Item::select('items.*',
            'orders.id AS order_id',
            'orders.state AS order_state',
            'orders.created_at AS order_created_at',
            'orders.updated_at AS order_updated_at',
            'orders.start AS order_start',
            'orders.end AS order_end')
            ->join('orders', 'orders.item_id', '=', 'items.id')
            ->join('meals', 'meals.id', '=', 'orders.meal_id')
            ->where('meals.id', $mealId)
            ->get();

        return response()->json(ItemOrderResource::collection($items), 200);
    }

    public function notDeliveredOrdersFromAMeal($mealId)
    {
        $items = Order::select('orders.*')
        ->join('meals', 'meals.id', '=', 'orders.meal_id')
        ->where([ ['meals.id', $mealId],
                  ['orders.state', '!=', 'delivered'],
                  ['orders.state', '!=', 'not delivered'],])
        ->get();

    return response()->json(OrderResource::collection($items), 200);
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
        $validatedData= $request->validate([
            'state' => 'in:pending,confirmed,in preparation,prepared,delivered,not delivered',
            'responsible_cook_id' => 'nullable|integer|exists:users,id',
            'item_id' => 'integer|exists:items,id',
            'meal_id' => 'integer|exists:meals,id',
            'start' => 'date'
        ]);

        $order = Order::findOrFail($id);
        $order->update($request->all());
        return new OrderResource($order);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $order = Order::findOrFail($id);

        $meal = Meal::findOrFail($order->meal_id);
        $item = Item::findOrFail($order->item_id);
        $meal->total_price_preview -= $item->price;
        $meal->save();

        $order->delete();
        return response()->json(null, 204);
    }
}
