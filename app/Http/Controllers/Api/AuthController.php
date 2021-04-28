<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\LoginSession;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    private function setLocationData($arrayToFill)
    {
        $locArray = geoip()->getLocation($arrayToFill['ip_address'])->toArray();
        if ($locArray['default'] == false) {
            $arrayToFill['country'] = $locArray['country'];
            $arrayToFill['city'] = $locArray['city'];
            $arrayToFill['postal_code'] = $locArray['postal_code'];
            $arrayToFill['latitude'] = $locArray['lat'];
            $arrayToFill['longitude'] = $locArray['lon'];
        }
        return $arrayToFill;
    }


    public function login(Request $request)
    {
        Log::channel('stderr')->info("POST LOGIN REQUEST ---> Entered the request");
        $validator = Validator::make($request->only('email', 'password'), [
            'email' => 'bail|required|string|email|max:255',
            'password' => ['bail', 'required', 'string',
                'size:64', 'regex:/^[\s\da-f0-9]*$/']
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()[0]], 400);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

//            if ($user->login_session_id != null) {
//                Log::channel('stderr')->info("POST LOGIN REQUEST ---> Currently logged in. Returning...");
//                return response()->json(['error' => 'Currently logged in'], 400);
//            }

            $loginSessionInfo = [
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'currently_active' => true
            ];
            $loginSession = new LoginSession($this->setLocationData($loginSessionInfo));
            $loginSession->save();

            $success['token'] = $user->createToken('VaultSec-token')->accessToken;
            Log::channel('stderr')->info("POST LOGIN REQUEST ---> Token created. Returning...");
            return response()->json(['success' => $success], 200);
//
//
//            if (!empty($user->oAuthAccessToken)) {
//                if (($user->oAuthAccessToken->expires_at > now()
//                        && $user->oAuthAccessToken->revoked == false)
//                    && $user->login_session_id != null) {
//
//                    Log::channel('stderr')->info("POST LOGIN REQUEST ---> Currently logged in. Returning...");
//                    return response()->json(['error' => 'Currently logged in'], 400);
//
//                } else if ($user->login_session_id != null
//                    && ($user->oAuthAccessToken->expires_at < now()
//                        || $user->oAuthAccessToken->revoked == true)) {
//
//                    Log::channel('stderr')->info("POST LOGIN REQUEST ---> HERE 1. Returning...");
//                    $success['token'] = $user->createToken('VaultSec-token')->accessToken;
//                    return response()->json(['success' => $success], 200);
//
//                } else if ($user->login_session_id == null
//                    && ($user->oAuthAccessToken->expires_at > now()
//                        && $user->oAuthAccessToken->revoked == false)) {
//                    $loginSessionInfo = [
//                        'user_id' => $user->id,
//                        'ip_address' => $request->ip(),
//                        'currently_active' => true
//                    ];
//                    $loginSession = new LoginSession($this->setLocationData($loginSessionInfo));
//                    $loginSession->save();
//                    $user->login_session_id = $loginSession->id;
//                    $user->save();
//
//                    Log::channel('stderr')->info("POST LOGIN REQUEST ---> HERE 2. Returning...");
//                    $success['success'] = "Successfully logged in";
//                    return response()->json(['success' => $success], 200);
//                } else if ($user->login_session_id == null
//                    && ($user->oAuthAccessToken->expires_at < now()
//                        || $user->oAuthAccessToken->revoked == true)) {
//                    $loginSessionInfo = [
//                        'user_id' => $user->id,
//                        'ip_address' => $request->ip(),
//                        'currently_active' => true
//                    ];
//                    $loginSession = new LoginSession($this->setLocationData($loginSessionInfo));
//                    $loginSession->save();
//                    $user->login_session_id = $loginSession->id;
//                    $user->save();
//
//                    Log::channel('stderr')->info("POST LOGIN REQUEST ---> HERE 3. Returning...");
//                    $success['token'] = $user->createToken('VaultSec-token')->accessToken;
//
//                    return response()->json(['success' => $success], 200);
//                }
//            } else {
//                $loginSessionInfo = [
//                    'user_id' => $user->id,
//                    'ip_address' => $request->ip(),
//                    'currently_active' => true
//                ];
//                $loginSession = new LoginSession($this->setLocationData($loginSessionInfo));
//                $loginSession->save();
//                $user->login_session_id = $loginSession->id;
//                $user->save();
//
//                Log::channel('stderr')->info("POST LOGIN REQUEST ---> HERE 4");
//                $success['token'] = $user->createToken('VaultSec-token')->accessToken;
//                return response()->json(['success' => $success], 200);
//            }
        } else {
            Log::channel('stderr')->info("POST LOGIN REQUEST ---> User not identified. Returning...");
            return response()->json(['error' => 'Unauthorized'], 400);
        }
    }

    public function register(Request $request)
    {
        Log::channel('stderr')->info("POST REGISTER REQUEST ---> Entered the request");
        $validator = Validator::make($request->all(), [
            'first_name' => 'bail|required|string|max:30|regex:/^[A-Z][a-zA-Z]+$/',
            'last_name' => 'bail|required|string|max:30|regex:/\b([A-Z][-,a-z. \']+[ ]*)+/',
            'email' => 'bail|required|string|email|max:255|unique:users',
            'password' => ['bail', 'required', 'string',
                'size:64', 'regex:/^[\s\da-f0-9]*$/',
                'confirmed'],
//            'password' => ['bail', 'required', 'string',
//                'min:30', 'max:66', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*(_|[^\w])).+$/',
//                'confirmed'],
            'ip_address' => 'bail|nullable|string|ip',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()[0]], 400);
        }

        $input = $request->all();
        $input['ip_address'] = $request->ip();
        $input['password'] = bcrypt($input['password']);
        User::create($this->setLocationData($input));

        Log::channel('stderr')->info("POST REGISTER REQUEST ---> Created user. Returning...");
        return response()->json(['success' => "Account created successfully"], 200);
    }

    /*
     * OAuth token is revoked and the current login session is disabled
     */
    public function logout()
    {
        Log::channel('stderr')->info("POST LOGOUT REQUEST ---> Entered the request");
        $user = Auth::user();
        $user->token()->revoke();
        Log::channel('stderr')->info("POST LOGOUT REQUEST ---> Revoked the token");
        $user->save();

        $time = Carbon::parse($user->token()->getAttributeValue('created_at'));
        $timePlusOne = Carbon::parse($user->token()->getAttributeValue('created_at'))->addSecond();
        DB::table('login_sessions')
            ->where('user_id', '=', $user->id)
            ->where('currently_active', '=', true)
            ->whereBetween('created_at', [$time, $timePlusOne])
            ->update(['currently_active' => false]);

        Log::channel('stderr')->info("POST LOGOUT REQUEST ---> Logged out. Returning...");
        return response()->json(['success' => 'Logged out'], 200);
    }
}
