<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class UserController extends Controller
{
    public function userInformation()
    {
        $user = Auth::user();
        $user->oAuthAccessToken;
        $user->loginSession;
        $user->vaultPassword;
        return response()->json(['success' => $user], 200);
    }

    public function allUsers()
    {
        $users = User::with('oAuthAccessToken')
            ->with('loginSession')
            ->with('vaultPassword')
            ->get();
        return response()->json(['success' => $users]);
    }

    public function currentlyActiveUsers()
    {
        $users = User::whereNotNull('login_session_id')->get();
        return response()->json(['success' => $users], 200);
    }

    public function getRandom()
    {
        $response = Http::post('https://api.random.org/json-rpc/2/invoke', [
            'jsonrpc' => '2.0',
            'method' => 'generateIntegers',
            'params' => [
                'apiKey' => env('RANDOM_ORG_API_KEY'),
                'n' => 1,
                'min' => 10000000,
                'max' => 1000000000,
            ],
            'id' => 42
        ]);
        return response($response->json());
    }
}
