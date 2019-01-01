<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\DB;

define('YOUR_SERVER_URL', 'http://project.dad');
/* Check "oauth_clients" table for next 2 values: */
define('CLIENT_ID', '2');
define('CLIENT_SECRET','aFH6g9BrJw2QQ5IVAFXnHaK6oOfiRODMEhbPawGs');

class LoginControllerAPI extends Controller
{
    public function login(Request $request)
    {
        $userCredLabel = 'email';
        $userCred = $request->email;
        if (empty($userCred)) {
            $userCredLabel = 'username';
            $userCred = $request->username;
        }
        $user = User::where($userCredLabel, $userCred)->first();

        if ($user->blocked == 1) {
            return response()->json("Unauthorized, user is blocked", 401);
        } else if ($user->email_verified_at == null) {
            return response()->json("Unauthorized, user needs to verify email", 401);
        }

        $http = new \GuzzleHttp\Client;
        $response = $http->post(YOUR_SERVER_URL . '/oauth/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => CLIENT_ID,
                'client_secret' => CLIENT_SECRET,
                'username' => $user->email,
                'password' => $request->password,
                'scope' => ''
            ],
            'exceptions' => false,
        ]);

        $errorCode = $response->getStatusCode();
        if ($errorCode == '200') {
            return json_decode((string)$response->getBody(), true);
        } else {
            return response()->json("User credentials are invalid", $errorCode);
           /* return response()->json(
                ['msg' => 'User credentials are invalid'],
                $errorCode
            );*/
        }
    }

    public function logout()
    {
        \Auth::guard('api')->user()->token()->revoke();
        \Auth::guard('api')->user()->token()->delete();
        return response()->json(['msg' => 'Token revoked'], 200);
    }
}
