<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\VaultPaymentCard;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VaultPaymentCardController extends Controller
{
    private $whitelist = array('id', 'title', 'card_number', 'expiration_mm', 'expiration_yy', 'type', 'cvv', 'pin', 'created_at_device', 'updated_at_device');
    private $blacklist = array('user_id', 'ip_address', 'currently_shared', 'created_at', 'updated_at');

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
            'title' => 'bail|nullable|string|max:1000',
            'card_number' => 'bail|string|max:500|required',
            'expiration_mm' => 'bail|required|max:400|string',
            'expiration_yy' => 'bail|required|max:400|string',
            'type' => 'bail|required|string|max:100',
            'cvv' => 'bail|required|string|max:400',
            'pin' => 'bail|required|string|max:400',
            'created_at_device' => 'bail|required|date_format:Y-m-d H:i:s',
            'updated_at_device' => 'bail|required|date_format:Y-m-d H:i:s'
        ]);
    }

    private function validatorForAnArray(Request $request)
    {
        return Validator::make($request->only($this->whitelist), [
            '*.id' => 'bail|required|integer',
            '*.title' => 'bail|nullable|string|max:1000',
            '*.card_number' => 'bail|string|max:500|required',
            '*.expiration_mm' => 'bail|required|max:400|string',
            '*.expiration_yy' => 'bail|required|max:400|string',
            '*.type' => 'bail|required|string|max:100',
            '*.cvv' => 'bail|required|string|max:400',
            '*.pin' => 'bail|required|string|max:400',
            '*.created_at_device' => 'bail|required|date_format:Y-m-d H:i:s',
            '*.updated_at_device' => 'bail|required|date_format:Y-m-d H:i:s'
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        Log::channel('stderr')->info("GET CARDS REQUEST ---> Entered the request");
        $vaultPaymentCards = VaultPaymentCard::where('user_id', '=', Auth::id())->get();
        $vaultPaymentCardsSterilized = $this->sterilizeOutput($vaultPaymentCards->toArray());
        Log::channel("info_channel")->info("vaultPaymentCardsSterilized ", $vaultPaymentCardsSterilized);

        Log::channel('stderr')->info("GET CARDS REQUEST ---> Got items. Returning...");
        return response()->json($vaultPaymentCardsSterilized, 200);
    }

    public function restoreDeleted(Request $request)
    {
        Log::channel('stderr')->info("POST CARDS RESTORE REQUEST ---> Entered the request");

        $validator = $this->validatorForAnArray($request);
        if ($validator->fails()) {
            Log::channel('stderr')->info($validator->errors()->all()[0]);
            return response()->json(['error' => $validator->errors()->all()[0]], 400);
        }
        $input = $request->all();
        $input = $this->sterilizeInputArray($input, $request->ip());

        Log::channel("info_channel")->info("restoreCards input cleaned", $input);
        Log::channel('stderr')->info("POST CARDS RESTORE REQUEST ---> Cleaned up input");

        $idsArray = array_map(
            function ($functionArray) {
                return array_intersect_key($functionArray, array_flip(array('id')));
            }, $input
        );

        if (!VaultPaymentCard::insert($input)) {
            Log::channel('stderr')->info("POST CARDS RESTORE REQUEST ---> Failed to insert");
            return response()->json(['error' => "Failed to insert"], 400);
        }

        $restoredVaultPaymentCards = VaultPaymentCard::where('user_id', '=', Auth::id())
            ->whereIn('id', $idsArray)->get();
        Log::channel('stderr')->info("POST CARDS RESTORE REQUEST ---> Got deleted items");

        $restoredVaultPaymentCardsSterilized = $this->sterilizeOutput($restoredVaultPaymentCards->toArray());

        Log::channel("info_channel")->info("restoredVaultPaymentCardsSterilized : ", $restoredVaultPaymentCardsSterilized);
        Log::channel('stderr')->info("POST CARDS RESTORE REQUEST ---> Sterilized items. Returning...");
        return response()->json($restoredVaultPaymentCardsSterilized, 201);
    }

    public function storeSingle(Request $request)
    {
        Log::channel('stderr')->info("POST SINGLE CARD REQUEST ---> Enteted the request");

        $validator = $this->validatorForASingleObject($request);
        if ($validator->fails()) {
            Log::channel('stderr')->info($validator->errors()->all()[0]);
            return response()->json(['error' => $validator->errors()->all()[0]], 400);
        }
        $input = $request->only($this->whitelist);
        $input = $this->sterilizeInput($input, $request->ip());

        Log::channel("info_channel")->info("storeSingle input sterilized : ", $input);
        Log::channel('stderr')->info("POST SINGLE CARD REQUEST ---> Cleaned up input");

        $cardId = VaultPaymentCard::create($input)->id;

        Log::channel('stderr')->info("POST SINGLE CARD REQUEST ---> Inserted item. Got item ID. Returning...");
        return response()->json($cardId, 201);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        Log::channel('stderr')->info("POST CARDS SYNC REQUEST ---> Entered the request");

        $validator = $this->validatorForAnArray($request);
        if ($validator->fails()) {
            Log::channel('stderr')->info($validator->errors()->all()[0]);
            return response()->json(['error' => $validator->errors()->all()[0]], 400);
        }
        $input = $request->all();
        $input = $this->sterilizeInputArray($input, $request->ip());

        Log::channel("info_channel")->info("sync cards input sterilized : ", $input);
        Log::channel('stderr')->info("POST CARDS SYNC REQUEST ---> Cleaned up input");

        VaultPaymentCard::upsert($input, 'id');

        Log::channel('stderr')->info("POST CARDS SYNC REQUEST ---> Inserted or updated items");

        $vaultPaymentCards = VaultPaymentCard::where('user_id', '=', Auth::id())->get();
        Log::channel("info_channel")->info("Payment cards sync to return :", $vaultPaymentCards->toArray());

        $vaultPaymentCardsSterilized = $this->sterilizeOutput($vaultPaymentCards->toArray());

        Log::channel('stderr')->info("POST CARDS SYNC REQUEST ---> Got and sterilized items. Returning...");
        return response()->json($vaultPaymentCardsSterilized, 201);
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
        Log::channel('stderr')->info("PUT UPDATE CARD REQUEST ---> Entered the request");

        $validator = $this->validatorForASingleObject($request);
        if ($validator->fails()) {
            Log::channel('stderr')->info($validator->errors()->all()[0]);
            return response()->json(['error' => $validator->errors()->all()[0]], 400);
        }
        $input = $request->only($this->whitelist);
        $input = $this->sterilizeInput($input, $request->ip());

        Log::channel("info_channel")->info("Update card input after sterilization", $input);
        Log::channel('stderr')->info("PUT UPDATE CARD REQUEST ---> Cleaned up input");

        $vaultPaymentCard = VaultPaymentCard::where('user_id', '=', Auth::id())->findOrFail($id);
        $vaultPaymentCard->update($input);
        $vaultPaymentCard->save();

        Log::channel('stderr')->info("PUT UPDATE CARD REQUEST ---> Updated item. Returning...");
        return response()->json($vaultPaymentCard->id, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        Log::channel('stderr')->info("DELETE CARDS REQUEST ---> Entered the request");

        $validator = Validator::make($request->all(), [
            "*" => 'bail|integer|min:0'               // [40, 50]
        ]);
        if ($validator->fails()) {
            Log::channel('stderr')->info($validator->errors()->all()[0]);
            return response()->json(['error' => $validator->errors()->all()[0]], 400);
        }
        $input = $request->all();
        VaultPaymentCard::where('user_id', '=', Auth::id())
            ->whereIn('id', $input)->delete();

        Log::channel('stderr')->info("DELETE CARDS REQUEST ---> Deleted items. Returning...");
        return response()->json(['success' => 'Records deleted'], 200);
    }
}
