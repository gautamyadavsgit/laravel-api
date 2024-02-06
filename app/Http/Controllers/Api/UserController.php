<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserValidation;
use Exception;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\Process\ExecutableFinder;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = User::all();
        if ($user->isEmpty()) {

            return response()->json(['error' => 'No users found'], 400);
        } else {

            return response()->json(['data' => $user->toArray()], 400);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8'],
            'password_confirmation' => ['required', 'same:password'],
        ]);
        // dd($validator->validated());
        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        } else {
            DB::beginTransaction();
            try {
                User::create($validator->validated());
                DB::commit();
                return response()->json(['success' => 'User Created Successfully'], 201);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json($e, 500);
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        } else {
            return response()->json(['data' => $user->toArray()], 201);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //  dd($request->all());
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string',
            'email' => ['sometimes', 'required', 'email', 'unique:users,email'],
            'contact' => ['sometimes', 'required'],
            'password' => ['sometimes', 'required', 'min:8'],
            'password_confirmation' => ['sometimes', 'required', 'same:password'],
        ]);
        DB::beginTransaction();
        try {
            $user = User::findOrfail($id);
            // dd($request->all());
            $user->update(array_filter($validatedData));
            DB::commit();
            return response()->json(['Success' => 'User update successfully']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['Error' => 'User not found']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 200);
        } else {
            DB::beginTransaction();

            try {
                $user->delete();
                DB::commit();
                return response()->json(['success' => 'User Deleted successfully'], 500);
            } catch (Exception $e) {
                DB::rollBack();
            }
        }
    }
    public function change_password(Request $request, string $id)
    {
        //  dd($request);
        $validatedData = Validator::make($request->all(),[
            'old_password' => 'required',
            'password' => 'required|string',
            'confirmed_password' => 'required_if:password,!=,null|string|same:password',
        ]);
        if($validatedData->fails()){
            return response()->json($validatedData->messages(), 400);
        }
        DB::beginTransaction();
        try {
            $user = User::findOrfail($id);
            // dd($request->all());
            // dd($request->input('old_password'));
            if(Hash::check($request->input('old_password'),$user->password)){
                $user->update(['password' => $request->input('password')]);
                DB::commit();
                return response()->json(['Success' => 'User update successfully']);
            }else{
                return response()->json(['ERROR' => 'password not match']);

            }
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['Error' => 'User not found']);
        }
    }
}
