<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\VaultNote;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VaultNoteController extends Controller
{

    private $whitelist = array('id', 'title', 'text', 'color', 'font_size', 'created_at_device', 'updated_at_device');
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
            'text' => 'bail|required|string|max:10000',
            'color' => ['bail', 'string', 'max:10', 'required',
                'regex:/^(\#[\da-f]{3}|\#[\da-f]{6})$/',
            ],
            'font_size' => 'bail|integer|max:22|min:12|required',
            'created_at_device' => 'bail|required|date_format:Y-m-d H:i:s',
            'updated_at_device' => 'bail|required|date_format:Y-m-d H:i:s'
        ]);
    }

    private function validatorForAnArray(Request $request)
    {
        return Validator::make($request->only($this->whitelist), [
            '*.id' => 'bail|required|integer',
            '*.title' => 'bail|nullable|string|max:500',
            '*.text' => 'bail|required|string|max:10000',
            '*.color' => ['bail', 'string', 'max:10', 'required',
                'regex:/^(\#[\da-f]{3}|\#[\da-f]{6})$/',
            ],
            '*.font_size' => 'bail|integer|max:22|min:12|required',
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
        Log::channel('stderr')->info("GET NOTES REQUEST ---> Entered the request");

        $vaultNotes = VaultNote::where('user_id', '=', Auth::id())->get();
        $vaultNotesSterilized = $this->sterilizeOutput($vaultNotes->toArray());
        Log::channel("info_channel")->info("vaultNotesSterilized ", $vaultNotesSterilized);

        Log::channel('stderr')->info("GET NOTES REQUEST ---> Got items. Returning...");
        return response()->json($vaultNotesSterilized, 200);
    }

    public function indexAdmin()
    {
        $vaultNotes =
            VaultNote::leftJoin('users', 'users.id', '=', 'vault_notes.user_id')->get();
        return response()->json(['success' => $vaultNotes], 200);
    }

    /*
     * Created this endpoint instead of using the 'store' endpoint, because I wanted it to only return the notes
     * that were deleted, instead of returning all of them, deleting them all in the mobile app and inserting them.
     * Now, the app only has to insert the deleted notes and be done with it
     * */
    public function restoreDeleted(Request $request)
    {
        Log::channel('stderr')->info("POST NOTES RESTORE REQUEST ---> Entered the request");

        $validator = $this->validatorForAnArray($request);
        if ($validator->fails()) {
            Log::channel('stderr')->info($validator->errors()->all()[0]);
            return response()->json(['error' => $validator->errors()->all()[0]], 400);
        }
        $input = $request->all();

        $input = $this->sterilizeInputArray($input, $request->ip());

        Log::channel("info_channel")->info("restoreNotes input cleaned", $input);

        Log::channel('stderr')->info("POST NOTES RESTORE REQUEST ---> Cleaned up input");

        $idsArray = array_map(
            function ($functionArray) {
                return array_intersect_key($functionArray, array_flip(array('id')));
            }, $input
        );

//        $restorationDate = Carbon::now();
//        $restorationDatePlusOne = Carbon::now()->addSecond();
        if (!VaultNote::insert($input)) {
            Log::channel('stderr')->info("POST NOTES RESTORE REQUEST ---> Failed to insert");
            return response()->json(['error' => "Failed to insert"], 400);
        }

        /*
         * To get deleted notes by the time that they were inserted back
         * */
//        $restoredVaultNotes = VaultNote::where('user_id', '=', Auth::id())
//            ->whereBetween('created_at', [$restorationDate, $restorationDatePlusOne])
//            ->get();

        $restoredVaultNotes = VaultNote::where('user_id', '=', Auth::id())
            ->whereIn('id', $idsArray)->get();

        Log::channel('stderr')->info("POST NOTES RESTORE REQUEST ---> Got deleted items");

        $restoredVaultNotesSterilized = $this->sterilizeOutput($restoredVaultNotes->toArray());

        Log::channel("info_channel")->info("restoredNotesSterilized : ", $restoredVaultNotesSterilized);
        Log::channel('stderr')->info("POST NOTES RESTORE REQUEST ---> Sterilized items. Returning...");
        return response()->json($restoredVaultNotesSterilized, 201);
    }

    public function storeSingle(Request $request)
    {
        Log::channel('stderr')->info("POST SINGLE NOTE REQUEST ---> Enteted the request");

        $validator = $this->validatorForASingleObject($request);
        if ($validator->fails()) {
            Log::channel('stderr')->info($validator->errors()->all()[0]);
            return response()->json(['error' => $validator->errors()->all()[0]], 400);
        }
        $input = $request->only($this->whitelist);

        $input = $this->sterilizeInput($input, $request->ip());

        Log::channel("info_channel")->info("Input after sterilization : ", $input);

        Log::channel('stderr')->info("POST SINGLE NOTE REQUEST ---> Cleaned up input");

        $noteId = VaultNote::create($input)->id;

        Log::channel('stderr')->info("POST SINGLE NOTE REQUEST ---> Inserted item. Got item ID. Returning...");
        return response()->json($noteId, 201);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        Log::channel('stderr')->info("POST NOTES SYNC REQUEST ---> Entered the request");
        $validator = $this->validatorForAnArray($request);
        if ($validator->fails()) {
            Log::channel('stderr')->info($validator->errors()->all()[0]);
            return response()->json(['error' => $validator->errors()->all()[0]], 400);
        }
        $input = $request->all();

        Log::channel("info_channel")->info("BEFORE STERILIZATION :", $input);

        $input = $this->sterilizeInputArray($input, $request->ip());

        Log::channel('stderr')->info("POST NOTES SYNC REQUEST ---> Cleaned up input");
        Log::channel("info_channel")->info("AFTER STERILIZATION :", $input);

        VaultNote::upsert($input, 'id');

        Log::channel('stderr')->info("POST NOTES SYNC REQUEST ---> Inserted or updated items");

        $vaultNotes = VaultNote::where('user_id', '=', Auth::id())->get();
        Log::channel("info_channel")->info("Notes to return :", $vaultNotes->toArray());

        $vaultNotesSterilized = $this->sterilizeOutput($vaultNotes->toArray());

        Log::channel('stderr')->info("POST NOTES SYNC REQUEST ---> Got and sterilized items. Returning...");
        return response()->json($vaultNotesSterilized, 201);
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
        Log::channel('stderr')->info("PUT UPDATE NOTE REQUEST ---> Entered the request");

        $validator = $this->validatorForASingleObject($request);
        if ($validator->fails()) {
            Log::channel('stderr')->info($validator->errors()->all()[0]);
            return response()->json(['error' => $validator->errors()->all()[0]], 400);
        }
        $input = $request->only($this->whitelist);

        Log::channel("info_channel")->info("Input before anything", $input);

        $input = $this->sterilizeInput($input, $request->ip());

        Log::channel('stderr')->info("PUT UPDATE NOTE REQUEST ---> Cleaned up input");

        $vaultNote = VaultNote::where('user_id', '=', Auth::id())->findOrFail($id);
        $vaultNote->update($input);
        $vaultNote->save();

        Log::channel('stderr')->info("PUT UPDATE NOTE REQUEST ---> Updated item. Returning...");
        return response()->json($vaultNote->id, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(Request $request)
    {
        Log::channel('stderr')->info("DELETE NOTES REQUEST ---> Entered the request");
        $validator = Validator::make($request->all(), [
            "*" => 'bail|integer|min:0'               // [40, 50]
        ]);
        if ($validator->fails()) {
            Log::channel('stderr')->info($validator->errors()->all()[0]);
            return response()->json(['error' => $validator->errors()->all()[0]], 400);
        }
        $input = $request->all();
        VaultNote::where('user_id', '=', Auth::id())
            ->whereIn('id', $input)->delete();

        Log::channel('stderr')->info("DELETE NOTES REQUEST ---> Deleted items. Returning...");
        return response()->json(['success' => 'Records deleted'], 200);
    }
}
