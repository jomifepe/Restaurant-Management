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

////->->->->->->->->->->->->->->->->->->-INVOICES<->->->->->->->->->->->->->->

Route::group(['prefix' => 'invoices', 'middleware' => 'managerAndCashier'],
    function () {
        Route::get('{id}/meal', 'InvoiceControllerAPI@invoiceMeal')->name('invoices.meal');
        Route::get('details','InvoiceControllerAPI@allOrders')->name('invoices.details');
        Route::get('pending', 'InvoiceControllerAPI@pendingOrders')->name('invoices.pending');
        Route::get('paid', 'InvoiceControllerAPI@paidOrders')->name('invoices.paid');
        Route::get('{id}/items','InvoiceItemsControllerAPI@itemsFromAnInvoice')->name('invoices.items');
        /* Resource */
        Route::get('', 'InvoiceControllerAPI@index')->name('invoices.index');
        Route::match(['put', 'patch'],'{invoice}', 'InvoiceControllerAPI@update')->name('invoices.update');
        Route::get('{invoice}', 'InvoiceControllerAPI@show')->name('invoices.show');
    }
);

Route::post('invoices', 'InvoiceControllerAPI@store')->name('invoices.store')
->middleware('managerAndWaiter');

Route::delete('invoices/{invoice}', 'InvoiceControllerAPI@destroy')->name('invoice.destroy')
    ->middleware('manager');

Route::post('invoices/items', 'InvoiceItemsControllerAPI@store')->name('invoice.items.store')
    ->middleware('managerAndWaiter');

//<-<-<-<-<-<-<-<-<-<-<-<-<-<-<-<-<-<-<-INVOICES<-<-<-<-<-<-<-<-<-<-<-<-<-<-

////->->->->->->->->->->->->->->->->->->-MEALS<->->->->->->->->->->->->->->

Route::group(['prefix' =>'meals', 'middleware' => 'managerAndWaiter'],
    function () {
        Route::get('manager', 'MealControllerAPI@managerIndex')->name('meal.manager');
        Route::get('waiter/{waiterId}', 'MealControllerAPI@responsible')->name('meal.waiter');
        Route::get('table/{tableNumber}', 'MealControllerAPI@tableMeal')->name('meal.table');
        Route::get('{id}/tableNumber', 'MealControllerAPI@tableNumber')->name('meal.tableNumber');
        /* Resource */
        Route::get('', 'MealControllerAPI@index')->name('meals.index');
        Route::post('', 'MealControllerAPI@store')->name('meals.store');
        Route::match(['put', 'patch'], '{meal}' ,'MealControllerAPI@update')->name('meals.update');
        Route::get('{meal}', 'MealControllerAPI@show')->name('meals.show');
    }
);

Route::get('meals/{mealId}/waiter', 'MealControllerAPI@getWaiter')->name('meal.waiterresponsible')
    ->middleware('managerWaiterAndCook');

Route::delete('meals/{meal}', 'MealControllerAPI@destroy')->name('meal.destroy')
    ->middleware('manager');

//<-<-<-<-<-<-<-<-<-<-<-<-<-<-<-<-<-<-<-MEALS<-<-<-<-<-<-<-<-<-<-<-<-<-<-

////->->->->->->->->->->->->->->->->->->-USERS->->->->->->->->->->->->->->

Route::group(['prefix' => 'users', 'middleware' => 'manager'],
    function () {
        Route::get('all', 'UserControllerAPI@indexManager');
        /* Resource */
        Route::get('', 'UserControllerAPI@index')->name('users.index');
        Route::post('', 'UserControllerAPI@store')->name('users.store');
        Route::delete('{user}', 'UserControllerAPI@destroy')->name('users.destroy');
    }
);

Route::group(['prefix' => 'users', 'middleware' => 'auth:api'],
    function () {
        /* Worker photo update */
        Route::post('update/{id}', 'UserControllerAPI@postUpdate');
        /* Resource */
        Route::get('{user}', 'UserControllerAPI@show')->name('users.show');
        Route::match(['put', 'patch'], '{user}','UserControllerAPI@update')->name('users.update');
    }
);


Route::group(['prefix'=>'user', 'middleware' => 'manager'],
    function () {
        Route::put('{id}', 'UserControllerAPI@toggleBlockUser');
        Route::put('restore/{id}', 'UserControllerAPI@restore');
    }
);

//<-<-<-<-<-<-<-<-<-<-<-<-<-<-<-<-<-<-<-USERS<-<-<-<-<-<-<-<-<-<-<-<-<-<-

////->->->->->->->->->->->->->->->->->->-ITEMS->->->->->->->->->->->->->->

Route::group([ 'prefix' => 'items', 'middleware' => 'manager'],
    function () {
        Route::post('update/{id}', 'ItemControllerAPI@updatePost');
        Route::post('', 'ItemControllerAPI@store')->name('items.store');
        //fica aqui?
        Route::get('{item}', 'ItemControllerAPI@show')->name('items.show');
        Route::match(['put', 'patch'],'{item}','ItemControllerAPI@update')->name('items.update');
        Route::delete('{item}','ItemControllerAPI@destroy')->name('items.destroy');
    }
);
//precisos para mostrar menu não logado
Route::get('items', 'ItemControllerAPI@index')->name('items.index');
Route::get('items/type/{type}', 'ItemControllerAPI@showType')->name('items.type');

//<-<-<-<-<-<-<-<-<-<-<-<-<-<-<-<-<-<-<-ITEMS<-<-<-<-<-<-<-<-<-<-<-<-<-<-

////->->->->->->->->->->->->->->->->->->-ORDERS->->->->->->->->->->->->->->

Route::group(['prefix' => 'orders', 'middleware' => 'managerWaiterAndCook'],
    function () {
        /* get all the orders for a specific meal */
        Route::get('meal/{mealId}', 'OrderControllerAPI@mealOrders')->name('orders.meal');
        /* get all the items ordered for a specific meal */
        Route::get('meal/{mealId}/items', 'OrderControllerAPI@mealItems')->name('orders.meal.items');
        Route::get('', 'OrderControllerAPI@index')->name('orders.index');
        Route::match(['put', 'patch'],'{order}','OrderControllerAPI@update')->name('orders.update');
        Route::get('{order}', 'OrderControllerAPI@show')->name('orders.show');
    }
);

Route::get('users/email/{email}', 'UserControllerAPI@findUserByEmail')->name('user.email');

Route::post('orders', 'OrderControllerAPI@store')->name('orders.store')
    ->middleware('managerAndWaiter');

Route::get('orders/{id}/toprepare', 'OrderControllerAPI@toPrepare')->name('orders.toPrepare')
    ->middleware('managerAndCook');

Route::delete('orders/{order}', 'OrderControllerAPI@destroy')->name('orders.destroy')
    ->middleware('manager');

//<-<-<-<-<-<-<-<-<-<-<-<-<-<-<-<-<-<-<-ORDERS<-<-<-<-<-<-<-<-<-<-<-<-<-<-

////->->->->->->->->->->->->->->->->->->-TABLES->->->->->->->->->->->->->->

Route::group(['middleware' => 'manager'], function () {
    Route::apiResource('tables', 'TableControllerAPI');
});

//PREFIX TABLE
Route::put('table/restore/{id}', 'TableControllerAPI@restore')->middleware('manager');

//<-<-<-<-<-<-<-<-<-<-<-<-<-<-<-<-<-<-<-TABLES<-<-<-<-<-<-<-<-<-<-<-<-<-<-


