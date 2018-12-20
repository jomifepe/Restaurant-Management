<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\Item as ItemResource;
use App\Item;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Validator;


class ItemControllerAPI extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        return ItemResource::collection(Item::paginate(12));
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
            'name' => 'required|String|unique:items,name',
            'price' => 'required|numeric',
            'description' => 'required|String',
            'type' => 'required|in:drink,dish',
            'photo_url' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        $newItem = new Item();
        $newItem->fill($request->all());

        if ($request->hasFile('photo_url'))
        {
            $fileName = Storage::disk('public')->putFile('items', Input::file('photo_url'));
            $photoName = explode('/', $fileName)[1];
            $newItem->photo_url = $photoName;
        }

        $newItem->save();
        return response()->json(new ItemResource($newItem), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Item::findOrFail($id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function showType($showType)
    {
        return ItemResource::collection(Item::where('type', $showType)->get());
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

    }


   public function updatePost(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|String|unique:items,name,'.$id.',id',
            'price' => 'required|numeric',
            'description' => 'required|String',
            'type' => 'required|in:drink,dish',
            'photo_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        $oldItem = Item::findOrFail($id);

        if ($request->hasFile('photo_url'))
        {
            $fileName = Storage::disk('public')->putFile('items', Input::file('photo_url'));
            $newPhoto_url = explode('/', $fileName)[1];
            if($newPhoto_url != $oldItem->photo_url){
                $oldItem->photo_url = $newPhoto_url;
            }
        }

        $oldItem->name = $data['name'];
        $oldItem->price = $data['price'];
        $oldItem->description = $data['description'];
        $oldItem->type = $data['type'];

        $oldItem->save();
        return response()->json(new ItemResource($oldItem), 201);
    }




    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = Item::findOrFail($id);

        $restriction = DB::table('orders')->where('item_id', $id)->count();
        if($restriction>0) {
            return $this->softDelete($item);
        }

        $restriction += DB::table('invoice_items')->where('item_id', $id)->count();

        if($restriction>0){
            return $this->softDelete($item);
        }else{
            $item->forceDelete();
        }
        return response()->json($item, 204);
    }


    public function softDelete($item)
    {
        $item->delete();
        return response()->json($item, 204);
    }
}
