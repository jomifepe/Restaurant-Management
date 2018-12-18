<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

define('YOUR_SERVER_URL', 'http://project.dad');
// Check "oauth_clients" table for next 2 values:
define('CLIENT_ID', '2');
define('CLIENT_SECRET','m3UpumhdlfKYXAeAbpSY0MUOd1kHO0CAPZwIO1cg');

class LoginControllerAPI extends Controller
{
    public function login(Request $request)
    {
        $userEmail = $request->email;
        if (empty($userEmail)) {
            $user = User::where('username', $request->username)->first();
            $userEmail = $user->email;
        }

        $http = new \GuzzleHttp\Client;
        $response = $http->post(YOUR_SERVER_URL . '/oauth/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => CLIENT_ID,
                'client_secret' => CLIENT_SECRET,
                'username' => $userEmail,
                'password' => $request->password,
                'scope' => ''
            ],
            'exceptions' => false,
        ]);

        $errorCode = $response->getStatusCode();
        if ($errorCode == '200') {
            return json_decode((string)$response->getBody(), true);
        } else {
            return response()->json(
                ['msg' => 'User credentials are invalid'],
                $errorCode
            );
        }
    }

    public function logout()
    {
        \Auth::guard('api')->user()->token()->revoke();
        \Auth::guard('api')->user()->token()->delete();
        return response()->json(['msg' => 'Token revoked'], 200);
    }
}
