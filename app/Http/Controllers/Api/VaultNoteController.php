<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\VaultNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class VaultNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $vaultNotes = VaultNote::where('user_id', '=', Auth::id())->get();
        return response()->json(['success' => $vaultNotes], 200);
    }

    public function indexAdmin()
    {
        $vaultNotes =
            VaultNote::leftJoin('users', 'users.id', '=', 'vault_notes.user_id')->get();
        return response()->json(['success' => $vaultNotes], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'bail|required|string|max:30',
            'text' => 'bail|required|string|max:1024',
            'color' => 'bail|string|max:10',
            'font_size' => 'bail|integer|max:30',
            'ip_address' => 'bail|nullable|string|ip',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $input = $request->all();
        $input['user_id'] = Auth::id();
        $input['ip_address'] = $request->ip();
        $vaultNote = VaultNote::create($input);
        $vaultNote->save();

        return response()->json(['success' => $vaultNote], 201);
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
            'text' => 'bail|required|string|max:1024',
            'color' => 'bail|string|max:10',
            'font_size' => 'bail|integer|max:30',
            'ip_address' => 'bail|nullable|string|ip',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $vaultNote = VaultNote::findOrFail($id);

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
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $vaultNote = VaultNote::where('user_id', '=', Auth::id())
            ->findOrFail($id);
        $vaultNote->delete();
        return response()->json(['success' => 'Record deleted'], 200);
    }
}
