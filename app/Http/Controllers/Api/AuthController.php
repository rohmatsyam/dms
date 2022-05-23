<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends BaseController
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required',
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'telp' => 'required',
            // 'image' => 'required',
            'password' => 'required',
            'password_confirmation' => 'required|same:password'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        if ($file = $request->file('image')) {
            $uploadFolder = 'users';
            $image_uploaded_path = $file->store($uploadFolder);
            $user = User::create([
                'role_id' => $request->role_id,
                'name' => $request->name,
                'email' => $request->email,
                'telp' => $request->telp,
                'image' => Storage::disk('public')->url($image_uploaded_path),
                'password' => Hash::make($request->password),
            ]);
            if (!$user) {
                return $this->sendError("", "failed create user");
            }
        } else {
            $user = User::create([
                'role_id' => $request->role_id,
                'name' => $request->name,
                'email' => $request->email,
                'telp' => $request->telp,
                'image' => 'default.jpg',
                'password' => Hash::make($request->password),
            ]);
            if (!$user) {
                return $this->sendError("", "failed create user");
            }
        }

        $token = $user->createToken($request->email)->plainTextToken;

        return response()->json([
            'code' => 200,
            'data' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'message' => 'Success create user'
        ], 200);
    }
    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->sendError("",message:"wrong username or password");            
        }
        $user = User::where('email', $request['email'])->firstOrFail();
        $token = $user->createToken($request->email)->plainTextToken;
        return response()->json([
            'code' => 200,
            'data' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'message' => 'Success login to system'
        ], 200);
    }
    public function logout()
    {
        Auth::user()->tokens->each(function ($token) {
            $token->delete();
        });

        return $this->sendResponse("","Succes logout from system");        
    }
}
