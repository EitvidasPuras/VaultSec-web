<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\LoginSession;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    /*
     * The validator checks whether the request has valid variables or not
     * If valid, the server checks whether the credentials belong to a user
     * If they do, the server checks whether the user is not currently "logged in"
     * If not, the server creates a login session instance and saves it to the database, while
     *      also giving the user the current session id. Then the server checks whether the user
     *      has a valid OAuth token
     * If not, the server creates a new token and returns it to the user.
     */
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
            return response()->json(['error' => $validator->errors()->all()[0]], 400);
        }

        if (Auth::attempt(['email' => $request->email,
            'password' => $request->password])) {
            $user = Auth::user();

            if (!empty($user->oAuthAccessToken)) {
                if (($user->oAuthAccessToken->expires_at > now()
                        && $user->oAuthAccessToken->revoked == false)
                    && $user->login_session_id != null) {
                    return response()->json(['error' => 'Currently logged in'], 400);
                } else if ($user->login_session_id != null
                    && ($user->oAuthAccessToken->expires_at < now()
                        || $user->oAuthAccessToken->revoked == true)) {
                    $success['token'] = $user->createToken('VaultSec-token')->accessToken;
                    return response()->json(['success' => $success], 200);
                } else if ($user->login_session_id == null
                    && ($user->oAuthAccessToken->expires_at > now()
                        && $user->oAuthAccessToken->revoked == false)) {
                    $loginSessionInfo = [
                        'user_id' => $user->id,
                        'ip_address' => $request->ip(),
                        'currently_active' => true
                    ];
                    $loginSession = new LoginSession($this->setLocationData($loginSessionInfo));
                    $loginSession->save();
                    $user->login_session_id = $loginSession->id;
                    $user->save();

                    return response()->json(['success' => 'Successfully logged in'], 200);
                } else if ($user->login_session_id == null
                    && ($user->oAuthAccessToken->expires_at < now()
                        || $user->oAuthAccessToken->revoked == true)) {
                    $loginSessionInfo = [
                        'user_id' => $user->id,
                        'ip_address' => $request->ip(),
                        'currently_active' => true
                    ];
                    $loginSession = new LoginSession($this->setLocationData($loginSessionInfo));
                    $loginSession->save();
                    $user->login_session_id = $loginSession->id;
                    $user->save();

                    $success['token'] = $user->createToken('VaultSec-token')->accessToken;
                    return response()->json(['success' => $success], 200);
                }
            }
        } else {
            return response()->json(['error' => 'Unauthorized'], 400);
        }
    }

    /*
     * The validator checks whether the request has valid variables or not
     * If so, the user is created using all the variables, plus location variables
     * OAuth token is then created and returned.
     */
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
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()[0]], 400);
        }

        $input = $request->all();
        $input['ip_address'] = $request->ip();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($this->setLocationData($input));

        $success['token'] = $user->createToken('VaultSec-token')->accessToken;
        return response()->json(['success' => $success], 200);
    }

    /*
     * OAuth token is revoked and the current login session is disabled
     */
    public function logout()
    {
        $user = Auth::user();
        $user->oAuthAccessToken->revoked = 1;
        $user->oAuthAccessToken->save();
        $user->login_session_id = null;
        $user->save();

        DB::table('login_sessions')
            ->where('user_id', '=', $user->id)
            ->where('currently_active', '=', true)
            ->update(['currently_active' => false]);
        return response()->json(['success' => 'Logged out'], 200);
    }
}
