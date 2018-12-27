<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', 'LoginControllerAPI@login')->name('login');

Route::middleware('auth:api')->post('logout', 'LoginControllerAPI@logout');
Route::middleware('auth:api')->get('users/me', 'UserControllerAPI@myProfile');

Route::post('register', 'UserControllerAPI@store')->name('register');

Route::get('items', 'ItemControllerAPI@index');

Route::get('invoices/{id}/meal', 'InvoiceControllerAPI@invoiceMeal');
Route::get('invoices/details','InvoiceControllerAPI@allOrders');
Route::get('invoices/pending', 'InvoiceControllerAPI@pendingOrders')->name('invoices.pending');
Route::get('invoices/paid', 'InvoiceControllerAPI@paidOrders')->name('invoices.paid');
Route::get('invoices/{id}/items','InvoiceItemsAPI@itemsFromAnInvoice');
Route::get('invoices/{id}','InvoiceControllerAPI@orderDetails');
/**  USER **/
Route::get('users/all', 'UserControllerAPI@indexManager');
Route::put('user/{id}', 'UserControllerAPI@toggleBlockUser');
Route::put('user/restore/{id}', 'UserControllerAPI@restore');
Route::post('users/update/{id}', 'UserControllerAPI@postUpdate');

Route::apiResources(['meals'    => 'MealControllerAPI',
                     'invoices' => 'InvoiceControllerAPI',
                     'orders'   => 'OrderControllerAPI',
                     'items'    => 'ItemControllerAPI',
                     'users'    => 'UserControllerAPI',
                     'tables'    => 'TableControllerAPI']);


Route::post('items/update/{id}', 'ItemControllerAPI@updatePost');
Route::get('meals/{id}/tableNumber', 'MealControllerAPI@tableNumber')->name('meal.tableNumber');
Route::get('meals/waiter/{waiterId}', 'MealControllerAPI@responsible')->name('meal.waiter');
Route::get('meals/table/${tableNumber}', 'MealControllerAPI@tableMeal')->name('meal.table');

//get waiter id from a meal
Route::get('meals/{mealId}/waiter', 'MealControllerAPI@getWaiter')->name('meal.waiter');
/* get all the orders for a specific meal */
Route::get('orders/meal/{mealId}', 'OrderControllerAPI@mealOrders')->name('orders.meal');

/* get all the items ordered for a specific meal */
Route::get('orders/meal/{mealId}/items', 'OrderControllerAPI@mealItems')->name('orders.meal.items');



Route::get('items/type/{type}', 'ItemControllerAPI@showType')->name('items.type');



Route::put('table/restore/{id}', 'TableControllerAPI@restore');


Route::get('orders/{id}/toprepare', 'OrderControllerAPI@toPrepare')->name('orders.toPrepare');





