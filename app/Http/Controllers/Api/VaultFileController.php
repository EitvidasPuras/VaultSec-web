<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\VaultFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class VaultFileController extends Controller
{
    /*
     * This controller will be completed after the integration of file uploading will be done on the app
     * TODO: Complete this controller after the app starts sending requests with appropriate data
     */

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $vaultFiles = VaultFile::where('user_id', '=', Auth::id())->get();
        return response()->json(['success' => $vaultFiles], 200);
    }

    public function indexAdmin()
    {
        $vaultFiles =
            VaultFile::leftJoin('users', 'users.id', '=', 'vault_files.user_id')->get();
        return response()->json(['success' => $vaultFiles], 200);
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
            'file' => 'bail|required|file|max:7168', //Size in Kb
            'file_name' => 'bail|required|string|max:40',
            'file_extension' => 'bail|required|string|max:12',
            'file_size' => 'bail|required|integer|max:7340032', //Size in bytes
            'file_size_v' => 'bail|required|integer|max:7340032',
            'stored_file_name' => 'bail|string|required|max:1024',
            'ip_address' => 'bail|nullable|string|ip',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()[0]], 400);
        }

        $input = $request->all();
        $input['user_id'] = Auth::id();
        $input['ip_address'] = $request->ip();
        $request->file('file')->storeAs('',
            $input['stored_file_name'], 'private');
//        $input['base64'] = file_put_contents('');

        $vaultFile = VaultFile::create($input);
        $vaultFile->save();

        return response()->json(['success' => $vaultFile], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $vaultFile = VaultFile::where('user_id', '=', Auth::id())
            ->findOrFail($id);
//        $path = storage_path('stored_files') . "\\" . $vaultFile['stored_file_name'];
//        $header = [
//            'Location: http://127.0.0.1:8001/api/admin/active'
//        ];
//        return response()->download($path,'', $header);


        return response()->json(['success' => $vaultFile], 200);
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
            'file' => 'bail|required|file|size:7168', //Size in Kb
            'file_name' => 'bail|required|string|max:40',
            'file_extension' => 'bail|required|string|max:12',
            'file_size' => 'bail|required|integer|max:7340032', //Size in bytes
            'file_size_v' => 'bail|integer|required|max:7340032',
            'stored_file_name' => 'bail|string|required|max:1024',
            'ip_address' => 'bail|nullable|string|ip',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()[0]], 400);
        }

        $vaultFile = VaultFile::where('user_id', '=', Auth::id())
            ->findOrFail($id);

        $vaultFile->update([
            'file_name' => $request->file_name,
            'file_extension' => $request->file_extension,
            'file_size' => $request->file_size,
            'vault_file_size' => $request->vault_file_size,
            'stored_file_name' => $request->stored_file_name,
            'ip_address' => $request->ip(),
        ]);
        $request->file('file')->storeAs('',
            $request['stored_file_name'], 'private');

        $vaultFile->save();
        return response()->json(['success' => $vaultFile], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $vaultFile = VaultFile::where('user_id', '=', Auth::id())
            ->findOrFail($id);
        $vaultFile->delete();
        if (Storage::disk('private')->delete($vaultFile['stored_file_name'])) {
            return response()->json(['success' => 'File deleted'], 200);
        }
        return response()->json(['error' => 'Could not delete a file'], 400);
    }
}
