<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\User as UserResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

use App\User;
use App\StoreUserRequest;
use Hash;
use Illuminate\Support\Facades\Storage;

class UserControllerAPI extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('page')) {
            return UserResource::collection(User::paginate(5));
        } else {
            return UserResource::collection(User::all());
        }

        /*Caso não se pretenda fazer uso de Eloquent API Resources (https://laravel.com/docs/5.5/eloquent-resources), é possível implementar com esta abordagem:
        if ($request->has('page')) {
            return User::with('department')->paginate(5);;
        } else {
            return User::with('department')->get();;
        }*/
    }

    public function indexManager()
    {
        return UserResource::collection(User::withTrashed()->get());
    }

    public function toggleBlockUser($id)
    {
        $user = User::findOrFail($id);
        if($user->blocked == 1) {
            $action = 'unblocked';
            $user->blocked = 0;
        }else{
            $user->blocked = 1;
            $action = 'blocked';
        }
        $user->save();

        return $action;
    }


    public function show($id)
    {
        return new UserResource(User::find($id));
    }

    public function store(Request $request)
    {
        $request->validate([
                'name' => 'required|String',
                'username' => 'required|String|unique:users,username',
                'email' => 'required|email|unique:users,email',
                'password' => 'required',
                'type' => 'required|in:manager,cook,waiter,cashier',
                'photo_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);


        $newUser = new User();
        $newUser->fill($request->all());

        if ($request->hasFile('photo_url'))
        {
            $fileName = Storage::disk('public')->putFile('profiles', Input::file('photo_url'));
            $photoName = explode('/', $fileName)[1];
            $newUser->photo_url = $photoName;
        }

        $newUser->password = bcrypt($newUser->password);
        $newUser->save();
        return response()->json(new UserResource($newUser), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|min:3|regex:/^[A-Za-záàâãéèêíóôõúçÁÀÂÃÉÈÍÓÔÕÚÇ ]+$/',
            'username' => 'required|string|max:30',
            'email' => 'required|email|unique:users,email,'.$id
        ]);

        $user = User::findOrFail($id);
//        $user->photo_url = end(explode("/", $user->photo_url));
        $user->password = bcrypt($user->password);
        $user->name = $data["name"];
        $user->username = $data["username"];
        $user->email = $data["email"];
        $user->update();

        if ($request->current_password) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'message' => 'Incorrect current password',
                    'status' => 401
                ], 401);
            }

            $user->password = bcrypt($request->password);
        } else {
            $user->fill($request->all());
        }

        $user->save();
        return new UserResource($user);
    }



    public function postUpdate(Request $request, $id){
         $request->validate([
            'name' => 'required|String',
            'username' => 'required|String|unique:users,username,'.$id.',id',
            'email' => 'required|email|unique:users,email,'.$id.',id',
            //'type' => 'required|in:manager,cook,waiter,cashier',
            'photo_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);


        $user = User::findOrFail($id);

        $user->fill($request->all());

        if ($request->hasFile('photo_url'))
        {
            $fileName = Storage::disk('public')->putFile('profiles', Input::file('photo_url'));
            $photoName = explode('/', $fileName)[1];
            $user->photo_url = $photoName;
        }

        $user->password = bcrypt($user->password);
        $user->update();
        return new UserResource($user);
    }



    public function emailAvailable(Request $request)
    {
        $totalEmail = 1;
        if ($request->has('email') && $request->has('id')) {
            $totalEmail = DB::table('users')->where('email', '=', $request->email)->where('id', '<>', $request->id)->count();
        } else if ($request->has('email')) {
            $totalEmail = DB::table('users')->where('email', '=', $request->email)->count();
        }
        return response()->json($totalEmail == 0);
    }

    public function myProfile(Request $request)
    {
        return new UserResource($request->user());
    }


    public function restore($id)
    {
        $user = User::onlyTrashed()->where('id', $id)->firstOrFail();
        $user->restore();
        return response()->json(new UserResource($user), 200);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        try
        {
            $user->forceDelete();
        }
        catch(Exception $ex)
        {
            $user->softDelete = true;
            $user->delete();
        }

        return response()->json(null, 204);
    }




}
