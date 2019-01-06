<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\Meal as MealResource;
use App\Http\Resources\MealHR as MealHRResource;
use App\Http\Resources\Invoice as InvoiceResource;
use App\Meal;
use App\User;
use App\Table;
use App\Order;
use App\Item;
use App\Invoice;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MealControllerAPI extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index() //PARA O MANAGER
    {
        $items = Meal::select('meals.*', 'users.name AS responsible_waiter_name')
            ->join('users', 'users.id', '=', 'meals.responsible_waiter_id')
            ->get();

        return MealHRResource::collection($items);
    }

    public function managerIndex(){
        $items = Meal::select('meals.*', 'users.name AS responsible_waiter_name')
            ->join('users', 'users.id', '=', 'meals.responsible_waiter_id')
            ->get();

        return MealHRResource::collection($items);
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
            'table_number' => 'required|exists:restaurant_tables',
            'state' => 'required|in:active,terminated,paid,not paid',
            'start' => 'required|date',
            'responsible_waiter_id' => 'required|integer|exists:users,id'
        ]);

        $activeMeal = Meal::where('table_number', $request->table_number)
            ->where('state', 'active')->first();

        if (!empty($activeMeal)) {
            return response()->json([
                'message' => 'Table already has an active meal associated',
                'status' => 422
            ], 422);
        }

        $meal = new Meal();
        $meal->fill($request->all());
        $meal->save();
        return response()->json(new MealResource($meal), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return MealResource
     */
    public function show($id)
    {
        return new MealResource(Meal::find($id));
    }

    public function tableMeal($tableNumber) {
        return new MealResource(Meal::where('table_number', $tableNumber)->first());
    }

    public function tableNumber($id){
        $meal = Meal::findOrFail($id);
        return $meal->table_number;
    }

    public function terminate($mealId) {
        $meal = Meal::findOrFail($mealId);
        $notDeliveredOrders = Order::where('meal_id', $mealId)
            ->where('state', '<>', 'delivered')->get();

        foreach ($notDeliveredOrders as $order) {
            $order->state = 'not delivered';
            $order->end = Carbon::now();

            $item = Item::withTrashed()->where('id', $order->item_id)->first();
            $meal->total_price_preview = $meal->total_price_preview - $item->price;

            $order->save();
        }

        $meal->state = 'terminated';
        $meal->end = Carbon::now();
        $meal->save();

        return new MealResource($meal);
    }

    /**
     * Display the specified resource.
     *
     * @param $waiterId
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function responsible($waiterId)
    {
        return MealResource::collection(Meal::where('responsible_waiter_id', $waiterId)->get());
    }

    public function getWaiter($mealId)
    {
        $meal = Meal::findOrFail($mealId);
        $waiterId= $meal->responsible_waiter_id;

        return User::findOrFail($waiterId);

    }

    public function getInvoice($mealId)
    {
        $meal = Meal::findOrFail($mealId);

        // return Meal::select('invoices.id')
        // ->join('invoices', 'meals.id', '=', 'invoices.meal_id')
        // ->where('meals.id', $mealId)
        // ->first();

        return new InvoiceResource(Invoice::where('meal_id', $meal->id)->firstOrFail());
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
        $request->validate([
            'table_number' => 'exists:restaurant_tables',
            'state' => 'in:active,terminated,paid,not paid',
            'start' => 'date',
            'responsible_waiter_id' => 'integer|exists:users,id'
        ]);

        $meal = Meal::findOrFail($id);
        $meal->update($request->all());
        return new MealResource($meal);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $meal = Meal::findOrFail($id);
        $meal->delete();
        return response()->json(null, 204);
    }
}
