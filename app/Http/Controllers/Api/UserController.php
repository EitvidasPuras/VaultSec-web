<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;

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

    //TODO: VaultController for getting everything, seperate controllers for seperate models
}
