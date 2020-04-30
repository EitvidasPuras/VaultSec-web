<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\VaultPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class VaultPasswordController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $vaultPasswords = VaultPassword::where('user_id', '=', Auth::id())->get();
        return response()->json(['success' => $vaultPasswords], 200);
    }

    public function indexAdmin()
    {
        $vaultPasswords =
            VaultPassword::leftJoin('users', 'users.id', '=', 'vault_passwords.user_id')->get();
        return response()->json(['success' => $vaultPasswords], 200);
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
            'website_name' => 'bail|required|string|url|max:100',
            'login' => 'bail|string|max:200',
            'password' => 'bail|required|string|min:4',
            'category' => 'bail|required|string|max:40',
            'ip_address' => 'bail|nullable|string|ip',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $input = $request->all();
        $input['user_id'] = Auth::id();
        $input['ip_address'] = $request->ip();
        $vaultPassword = VaultPassword::create($input);
        $vaultPassword->save();

        return response()->json(['success' => $vaultPassword], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $vaultPassword = VaultPassword::where('user_id', '=', Auth::id())
            ->findOrFail($id);
        return response()->json(['success' => $vaultPassword], 200);
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
            'website_name' => 'bail|required|string|url|max:100',
            'login' => 'bail|string|max:200',
            'password' => 'bail|required|string|min:4',
            'category' => 'bail|required|string|max:40',
            'ip_address' => 'bail|nullable|string|ip',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $vaultPassword = VaultPassword::where('user_id', '=', Auth::id())
            ->findOrFail($id);

        $vaultPassword->update([
            'title' => $request->title,
            'website_name' => $request->website_name,
            'login' => $request->login,
            'password' => $request->password,
            'category' => $request->category,
            'ip_address' => $request->ip(),
        ]);
        $vaultPassword->save();
        return response()->json(['success' => $vaultPassword], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $vaultPassword = VaultPassword::where('user_id', '=', Auth::id())
            ->findOrFail($id);
        $vaultPassword->delete();
        return response()->json(['success' => 'Record deleted'], 200);
    }
}
