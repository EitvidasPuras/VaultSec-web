<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function userInformation()
    {
        $user = Auth::user();
        return response()->json(['success' => $user], 200);
    }
}
