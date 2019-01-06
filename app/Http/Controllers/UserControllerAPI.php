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
use Carbon\Carbon;

class UserControllerAPI extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('page')) {
            return UserResource::collection(User::paginate(5));
        } else {
            return UserResource::collection(User::all());
        }
    }

    public function indexManager()
    {
        return UserResource::collection(User::withTrashed()->orderBy('type')->get());
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

    public function showByType($type)
    {
        return UserResource::collection(User::where('type', $type)->get());
    }

    public function findUserByEmail($email) {
        $user = User::where('email', $email)->first();
        if (empty($user)) {
            return response()->json([
                'message' => 'Couldn\'t find a user with the provived email',
                'status' => 404
            ], 404);
        }
        return new UserResource($user);
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
        $request->validate([
            'name' => 'regex:/^[A-Za-záàâãéèêíóôõúçÁÀÂÃÉÈÍÓÔÕÚÇ ]+$/',
            'username' => 'string|max:30|unique:users,username,'.$id,
            'email' => 'email|unique:users,email,'.$id,
            'type' => 'string|in:manager,cook,waiter,cashier'
        ]);

        $user = User::findOrFail($id);
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

    public function verifyEmail($email) {
        $user = User::where('email', $email)->firstOrFail();
        $user->email_verified_at = Carbon::new();
        $user->save();
        return new UserResource($user);
    }

    public function postUpdate(Request $request, $id){
        $request->validate([
            'name' => 'regex:/^[A-Za-záàâãéèêíóôõúçÁÀÂÃÉÈÍÓÔÕÚÇ ]+$/',
            'username' => 'string|max:30|unique:users,username,'.$id,
            'email' => 'email|unique:users,email,'.$id,
            'type' => 'string|in:manager,cook,waiter,cashier',
            'photo_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        $user = User::findOrFail($id);

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

        if ($request->hasFile('photo_url'))
        {
            $fileName = Storage::disk('public')->putFile('profiles', Input::file('photo_url'));
            $photoPathParts = explode('/', $fileName);
            $user->photo_url = end($photoPathParts);
        }
        
        $user->save();
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
