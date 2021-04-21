<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\VaultPassword;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VaultPasswordController extends Controller
{

    private $whitelist = array('id', 'title', 'url', 'color', 'category', 'password', 'login', 'created_at_device', 'updated_at_device');
    private $blacklist = array('user_id', 'ip_address', 'currently_shared', 'created_at', 'updated_at');

    /*
     * Sterilizes the output before returning it.
     * It removes information that shouldn't be returned, for example 'user_id'.
     * */
    private function sterilizeOutput(array $array): array
    {
        return array_map(
            function ($functionArray) {
                return array_diff_key($functionArray,
                    array_flip($this->blacklist)
                );
            }, $array
        );
    }

    /*
     * Sterilizes the input before working with it.
     * It only keeps the keys that are inside the 'whitelist' array.
     * Also adds keys that needs to be inserted into the database.
     * */
    private function sterilizeInputArray(array $array, string $ip): array
    {
        return array_map(
            function ($functionArray) use ($ip) {
                $temporary = array_intersect_key($functionArray, array_flip($this->whitelist));

                return array_merge($temporary, [
                    'user_id' => Auth::id(),
                    'ip_address' => $ip,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }, $array
        );
    }

    private function sterilizeInput(array $array, string $ip): array
    {
        return array_merge($array, [
            'user_id' => Auth::id(),
            'ip_address' => $ip,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }

    private function validatorForASingleObject(Request $request)
    {
        return Validator::make($request->only($this->whitelist), [
            'id' => 'bail|required|integer',
            'title' => 'bail|nullable|string|max:500',
            'url' => 'bail|nullable|string|max:500',
            'color' => ['bail', 'string', 'max:10', 'required',
                'regex:/^(\#[\da-f]{3}|\#[\da-f]{6})$/',
            ],
            'login' => 'bail|nullable|max:3000|string',
            'password' => 'bail|required|max:10000|string',
            'category' => 'bail|required|string|max:20',
            'created_at_device' => 'bail|required|date_format:Y-m-d H:i:s',
            'updated_at_device' => 'bail|required|date_format:Y-m-d H:i:s'
        ]);
    }

    private function validatorForAnArray(Request $request)
    {
        return Validator::make($request->only($this->whitelist), [
            '*.id' => 'bail|required|integer',
            '*.title' => 'bail|nullable|string|max:500',
            '*.url' => 'bail|nullable|string|max:500',
            '*.color' => ['bail', 'string', 'max:10', 'required',
                'regex:/^(\#[\da-f]{3}|\#[\da-f]{6})$/',
            ],
            '*.login' => 'bail|nullable|max:3000|string',
            '*.password' => 'bail|required|max:10000|string',
            '*.category' => 'bail|required|string|max:20',
            '*.created_at_device' => 'bail|required|date_format:Y-m-d H:i:s',
            '*.updated_at_device' => 'bail|required|date_format:Y-m-d H:i:s'
        ]);
    }

    public function index()
    {
        Log::channel('stderr')->info("GET PASSWORDS REQUEST ---> Entered the request");
        $vaultPasswords = VaultPassword::where('user_id', '=', Auth::id())->get();
        $vaultPasswordsSterilized = $this->sterilizeOutput($vaultPasswords->toArray());
        Log::channel("info_channel")->info("vaultPasswordsSterilized ", $vaultPasswordsSterilized);

        Log::channel('stderr')->info("GET PASSWORDS REQUEST ---> Got items. Returning...");
        return response()->json($vaultPasswordsSterilized, 200);
    }

    public function indexAdmin()
    {
        $vaultPasswords =
            VaultPassword::leftJoin('users', 'users.id', '=', 'vault_passwords.user_id')->get();
        return response()->json(['success' => $vaultPasswords], 200);
    }

    public function restoreDeleted(Request $request)
    {
        Log::channel('stderr')->info("POST PASSWORDS RESTORE REQUEST ---> Entered the request");

        $validator = $this->validatorForAnArray($request);
        if ($validator->fails()) {
            Log::channel('stderr')->info($validator->errors()->all()[0]);
            return response()->json(['error' => $validator->errors()->all()[0]], 400);
        }
        $input = $request->all();
        $input = $this->sterilizeInputArray($input, $request->ip());

        Log::channel("info_channel")->info("restorePasswords input cleaned", $input);
        Log::channel('stderr')->info("POST PASSWORDS RESTORE REQUEST ---> Cleaned up input");

        $idsArray = array_map(
            function ($functionArray) {
                return array_intersect_key($functionArray, array_flip(array('id')));
            }, $input
        );

        if (!VaultPassword::insert($input)) {
            Log::channel('stderr')->info("POST PASSWORDS RESTORE REQUEST ---> Failed to insert");
            return response()->json(['error' => "Failed to insert"], 400);
        }

        $restoredVaultPasswords = VaultPassword::where('user_id', '=', Auth::id())
            ->whereIn('id', $idsArray)->get();
        Log::channel('stderr')->info("POST PASSWORDS RESTORE REQUEST ---> Got deleted items");

        $restoredVaultPasswordsSterilized = $this->sterilizeOutput($restoredVaultPasswords->toArray());

        Log::channel("info_channel")->info("restoredVaultPasswordsSterilized : ", $restoredVaultPasswordsSterilized);
        Log::channel('stderr')->info("POST PASSWORDS RESTORE REQUEST ---> Sterilized items. Returning...");
        return response()->json($restoredVaultPasswordsSterilized, 201);
    }

    public function storeSingle(Request $request)
    {
        Log::channel('stderr')->info("POST SINGLE PASSWORD REQUEST ---> Enteted the request");

        $validator = $this->validatorForASingleObject($request);
        if ($validator->fails()) {
            Log::channel('stderr')->info($validator->errors()->all()[0]);
            return response()->json(['error' => $validator->errors()->all()[0]], 400);
        }
        $input = $request->only($this->whitelist);
        $input = $this->sterilizeInput($input, $request->ip());

        Log::channel("info_channel")->info("storeSingle input sterilized : ", $input);
        Log::channel('stderr')->info("POST SINGLE PASSWORD REQUEST ---> Cleaned up input");

        $passwordId = VaultPassword::create($input)->id;

        Log::channel('stderr')->info("POST SINGLE PASSWORD REQUEST ---> Inserted item. Got item ID. Returning...");
        return response()->json($passwordId, 201);
    }

    public function store(Request $request)
    {
        Log::channel('stderr')->info("POST PASSWORDS SYNC REQUEST ---> Entered the request");

        $validator = $this->validatorForAnArray($request);
        if ($validator->fails()) {
            Log::channel('stderr')->info($validator->errors()->all()[0]);
            return response()->json(['error' => $validator->errors()->all()[0]], 400);
        }
        $input = $request->all();
        $input = $this->sterilizeInputArray($input, $request->ip());

        Log::channel("info_channel")->info("sync passwords input sterilized : ", $input);
        Log::channel('stderr')->info("POST PASSWORDS SYNC REQUEST ---> Cleaned up input");

        VaultPassword::upsert($input, 'id');

        Log::channel('stderr')->info("POST PASSWORDS SYNC REQUEST ---> Inserted or updated items");

        $vaultPasswords = VaultPassword::where('user_id', '=', Auth::id())->get();
        Log::channel("info_channel")->info("Passwords sync to return :", $vaultPasswords->toArray());

        $vaultPasswordsSterilized = $this->sterilizeOutput($vaultPasswords->toArray());

        Log::channel('stderr')->info("POST PASSWORDS SYNC REQUEST ---> Got and sterilized items. Returning...");
        return response()->json($vaultPasswordsSterilized, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        Log::channel('stderr')->info("PUT UPDATE PASSWORD REQUEST ---> Enteted the request");

        $validator = $this->validatorForASingleObject($request);
        if ($validator->fails()) {
            Log::channel('stderr')->info($validator->errors()->all()[0]);
            return response()->json(['error' => $validator->errors()->all()[0]], 400);
        }
        $input = $request->only($this->whitelist);
        $input = $this->sterilizeInput($input, $request->ip());

        Log::channel("info_channel")->info("Update password input after sterilization", $input);
        Log::channel('stderr')->info("PUT UPDATE PASSWORD REQUEST ---> Cleaned up input");

        $vaultPassword = VaultPassword::where('user_id', '=', Auth::id())->findOrFail($id);
        $vaultPassword->update($input);
        $vaultPassword->save();

        Log::channel('stderr')->info("PUT UPDATE PASSWORD REQUEST ---> Updated item. Returning...");
        return response()->json($vaultPassword->id, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(Request $request)
    {
        Log::channel('stderr')->info("DELETE PASSWORDS REQUEST ---> Entered the request");

        $validator = Validator::make($request->all(), [
            "*" => 'bail|integer|min:0'               // [40, 50]
        ]);
        if ($validator->fails()) {
            Log::channel('stderr')->info($validator->errors()->all()[0]);
            return response()->json(['error' => $validator->errors()->all()[0]], 400);
        }
        $input = $request->all();
        VaultPassword::where('user_id', '=', Auth::id())
            ->whereIn('id', $input)->delete();

        Log::channel('stderr')->info("DELETE PASSWORDS REQUEST ---> Deleted items. Returning...");
        return response()->json(['success' => 'Records deleted'], 200);
    }
}
