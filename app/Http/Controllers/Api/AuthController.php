<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
//        if ($this->hasTooManyLoginAttempts($request)) {
//            $this->fireLockoutEvent($request);
//
//            return $this->sendLockoutResponse($request);
//        }
        $validator = Validator::make($request->only('email', 'password'), [
            'email' => 'bail|required|string',
            'password' => 'bail|required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        if (Auth::attempt(['email' => $request->email,
            'password' => $request->password])) {
            $user = Auth::user();

            if (!empty($user->oAuthAccessToken)) {
                if ($user->oAuthAccessToken->expires_at > now()
                    && $user->oAuthAccessToken->revoked == false) {
                    return response()->json('Token still valid', 200);
                }
            }
            $success['token'] = $user->createToken('VaultSec')->accessToken;
            return response()->json(['success' => $success], 200);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'bail|required|string|max:30|regex:/^[a-zA-Z]+$/',
            'last_name' => 'bail|required|string|max:30|regex:/^[a-zA-Z]+$/',
            'email' => 'bail|required|string|email|max:255|unique:users',
            'password' => ['bail', 'required', 'string',
                'min:10', 'max:512', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*(_|[^\w])).+$/',
                'confirmed'],
            'ip_address' => 'bail|nullable|string|ip',
//            'country' => 'bail|nullable|string|max:30|regex:/^[a-zA-Z]+$/u',
//            'city' => 'bail|nullable|string|max:30|regex:/^[a-zA-Z]+$/u',
//            'postal_code' => 'bail|nullable|string|max:10',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $input = $request->all();
        $input['ip_address'] = $request->ip();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] = $user->createToken('VaultSec')->accessToken;
        $success['first_name'] = $user->first_name;
        return response()->json(['success' => $success], 200);
    }
}
