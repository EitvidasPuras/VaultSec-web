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

    private $whitelist = array('title', 'text', 'color', 'font_size', 'created_at_device', 'updated_at_device');
    private $blacklist = array('user_id', 'ip_address', 'currently_shared', 'created_at', 'updated_at');

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        error_log("GET NOTES REQUEST ---> Entered the request");
        $vaultNotes = VaultNote::where('user_id', '=', Auth::id())->get();
        $vaultNotesSterilized = array_map(
            function ($vaultNotes) {
                return array_diff_key($vaultNotes,
                    array_flip($this->blacklist)
                );
            }, $vaultNotes->toArray()
        );
        error_log("GET NOTES REQUEST ---> Got items. Returning...");
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
        error_log("POST NOTES RESTORE REQUEST ---> Entered the request");
        $validator = Validator::make($request->all(), [
            '*.title' => 'bail|nullable|string|max:30',
            '*.text' => 'bail|required|string|max:10000',
            '*.color' => ['bail', 'string', 'max:10', 'required',
                'regex:/^(\#[\da-f]{3}|\#[\da-f]{6})$/',
            ],
            '*.font_size' => 'bail|integer|max:22|min:12|required',
            '*.created_at_device' => 'bail|required|date_format:Y-m-d H:i:s',
            '*.updated_at_device' => 'bail|required|date_format:Y-m-d H:i:s'
        ]);

        if ($validator->fails()) {
            error_log($validator->errors()->all()[0]);
            return response()->json(['error' => $validator->errors()->all()[0]], 400);
        }
        $input = $request->all();
        /*
         * Remove any unnecessary fields that came with the request
         * */
        $input = array_map(
            function ($input) {
                return array_intersect_key(
                    $input,
                    array_flip($this->whitelist)
                );
            }, $input
        );
        /*
         * Add user id and ip address and timestamps to every object's array.
         * Timestamps don't get added when using insert
         * */
        $input = array_map(
            function ($input) use ($request) {
                return array_merge($input, [
                    'user_id' => Auth::id(),
                    'ip_address' => $request->ip(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }, $input
        );
        error_log("POST NOTES RESTORE REQUEST ---> Cleaned up input");
        $restorationDate = Carbon::now();
        $restorationDatePlusOne = Carbon::now()->addSecond();
        if (!VaultNote::insert($input)) {
            error_log("Failed to insert");
            return response()->json(['error' => "Failed to insert"], 400);
        }
        //TODO: Come back to it, since this is a pretty sketchy way of doing it.
        //  Maybe send over the notes with the ids and then get them
        $restoredVaultNotes = VaultNote::where('user_id', '=', Auth::id())
            ->whereBetween('created_at', [$restorationDate, $restorationDatePlusOne])
            ->get();

        error_log("POST NOTES RESTORE REQUEST ---> Got deleted items");
        Log::channel("info_channel")->info("restoredNotes", $restoredVaultNotes->toArray());

        $restoredVaultNotesSterilized = array_map(
            function ($vaultNotes) {
                return array_diff_key($vaultNotes,
                    array_flip($this->blacklist)
                );
            }, $restoredVaultNotes->toArray()
        );
        error_log("POST NOTES RESTORE REQUEST ---> Sterilized items. Returning...");
        return response()->json($restoredVaultNotesSterilized, 201);
    }

    public function storeSingle(Request $request)
    {
        error_log("POST SINGLE NOTE REQUEST ---> Enteted the request");
        $validator = Validator::make($request->all(), [
            'title' => 'bail|nullable|string|max:30',
            'text' => 'bail|required|string|max:10000',
            'color' => ['bail', 'string', 'max:10', 'required',
                'regex:/^(\#[\da-f]{3}|\#[\da-f]{6})$/',
            ],
            'font_size' => 'bail|integer|max:22|min:12|required',
            'created_at_device' => 'bail|required|date_format:Y-m-d H:i:s',
            'updated_at_device' => 'bail|required|date_format:Y-m-d H:i:s'
        ]);

        if ($validator->fails()) {
            error_log($validator->errors()->all()[0]);
            return response()->json(['error' => $validator->errors()->all()[0]], 400);
        }
        $input = $request->all();
        Log::channel("info_channel")->info("Input before anything", $input);

        /*
         * Clean up input by removing everything that isn't in whitelist
         * */
        $input = array_intersect_key(
            $input, array_flip($this->whitelist)
        );
        /*
         * Add user id and ip address and timestamps.
         * Even tho timestamps get added automatically when using create
         * */
        $input = array_merge($input, [
            'user_id' => Auth::id(),
            'ip_address' => $request->ip(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        error_log("POST SINGLE NOTE REQUEST ---> Cleaned up input");

        $noteId = VaultNote::create($input)->id;
        error_log("POST SINGLE NOTE REQUEST ---> Inserted item. Got item ID. Returning...");
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
        error_log("POST NOTES STORE REQUEST ---> Entered the request");
//        Log::channel("info_channel")->info($request);
        $validator = Validator::make($request->all(), [
            '*.title' => 'bail|nullable|string|max:30',
            '*.text' => 'bail|required|string|max:10000',
            '*.color' => ['bail', 'string', 'max:10', 'required',
                'regex:/^(\#[\da-f]{3}|\#[\da-f]{6})$/',
            ],
            '*.font_size' => 'bail|integer|max:22|min:12|required',
            '*.created_at_device' => 'bail|required|date_format:Y-m-d H:i:s',
            '*.updated_at_device' => 'bail|required|date_format:Y-m-d H:i:s'
        ]);
        if ($validator->fails()) {
            error_log($validator->errors()->all()[0]);
            return response()->json(['error' => $validator->errors()->all()[0]], 400);
        }
        $input = $request->all();
        Log::channel("info_channel")->info("REQUEST->all()", $request->all());
        /*
         * Remove any unnecessary fields that came with the request
         * */
        $input = array_map(
            function ($input) {
                return array_intersect_key(
                    $input,
                    array_flip($this->whitelist)
                );
            }, $input
        );
        /*
         * Add user id and ip address and timestamps to every object's array.
         * Timestamps don't get added when using insert
         * */
        $input = array_map(
            function ($input) use ($request) {
                return array_merge($input, [
                    'user_id' => Auth::id(),
                    'ip_address' => $request->ip(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }, $input
        );

        error_log("POST NOTES STORE REQUEST ---> Cleaned up input");
        Log::channel("info_channel")->info("INPUT :", $input);
        if (!VaultNote::insert($input)) {
            error_log("POST NOTES STORE REQUEST ---> Failed to insert");
            return response()->json(['error' => "Failed to insert"], 400);
        }
        error_log("POST NOTES STORE REQUEST ---> Inserted items");
        /*
         * In order to achieve complete synchronization with the server with only one request
         * after inserting the notes, the server returns all of them as a response
         * */
        $vaultNotes = VaultNote::where('user_id', '=', Auth::id())->get();
        Log::channel("info_channel")->info("Notes :", $vaultNotes->toArray());
        $vaultNotesSterilized = array_map(
            function ($vaultNotes) {
                return array_diff_key($vaultNotes,
                    array_flip($this->blacklist)
                );
            }, $vaultNotes->toArray()
        );
        error_log("POST NOTES STORE REQUEST ---> Got and sterilized items. Returning...");
        return response()->json($vaultNotesSterilized, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $vaultNote = VaultNote::where('user_id', '=', Auth::id())
            ->findOrFail($id);
        return response()->json(['success' => $vaultNote], 200);
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
        $validator = Validator::make($request->all(), [
            'title' => 'bail|required|string|max:30',
            'text' => 'bail|required|string|max:10000',
            'color' => 'bail|string|max:10',
            'font_size' => 'bail|integer|max:30',
            'ip_address' => 'bail|nullable|string|ip',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()[0]], 400);
        }

        $vaultNote = VaultNote::where('user_id', '=', Auth::id())
            ->findOrFail($id);

        $vaultNote->update([
            'title' => $request->title,
            'text' => $request->text,
            'color' => $request->color,
            'font_size' => $request->font_size,
            'ip_address' => $request->ip(),
        ]);

        $vaultNote->save();
        return response()->json(['success' => $vaultNote], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        error_log("DELETE NOTES REQUEST ---> Entered the request");
        $validator = Validator::make($request->all(), [
            "*" => 'bail|integer|min:0'               // [40, 50]
        ]);
        if ($validator->fails()) {
            error_log($validator->errors()->all()[0]);
            return response()->json(['error' => $validator->errors()->all()[0]], 400);
        }
        $input = $request->all();
        VaultNote::where('user_id', '=', Auth::id())
            ->whereIn('id', $input)->delete();
        error_log("DELETE NOTES REQUEST ---> Deleted items. Returning...");

        return response()->json(['success' => 'Records deleted'], 200);
    }
}
