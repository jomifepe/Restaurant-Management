<?php

namespace App\Http\Controllers;

use App\Table;
use App\Http\Resources\Table as TableResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class TableControllerAPI extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return TableResource::collection(Table::withTrashed()->get());
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
            'table_number' => 'required|numeric|unique:restaurant_tables,table_number',
        ]);

        $table = new Table();
        dd($request->input());

        if($request->table_number != null && $request->table_number != 0){
            $request->validate([
                'table_number' => 'required|numeric|unique:restaurant_tables,table_number']);
            $table->table_number = $request->table_number;
        }else{
            $table->table_number = (DB::table('restaurant_tables')->max('table_number')+1);
        }
        $table->fill($request->all());
        $table->save();

        return response()->json(new TableResource($table), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $table = Table::findOrFail($id);
        return response()->json(new TableResource($table), 200);
    }

    public function restore($id)
    {
        $table = Table::onlyTrashed()->where('table_number', $id)->firstOrFail();
        $table->restore();
        return response()->json(new TableResource($table), 200);
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
        dump($request);

        $updatedTable = $request->validate([
            'table_number' => 'required|numeric|unique:restaurant_tables,table_number,'.$id.',table_number',
            'created_at' => 'nullable|date',
            'updated_at' => 'nullable|date',
        ]);

        dd($updatedTable);
        $table = new Table();
        $table->fill($request->all());
        $table->save();
        return response()->json(new TableResource($table), 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $table = Table::findOrFail($id);
        $restriction = DB::table('meals')->where('table_number', $id)->count();
        if($restriction>0){
            return $this->softDelete($table);
        }else{
            $table->forceDelete();
        }
        return response()->json(null, 204);
    }


    public function softDelete($table)
    {
        $table->delete();
        return response()->json(null, 204);
    }
}
