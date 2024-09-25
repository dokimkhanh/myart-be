<?php

namespace App\Http\Controllers;

use App\Models\User;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register()
    {
        $credentials = request(['name', 'email', 'password']);

        $validator = Validator::make($credentials, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $credentials['email'] = strtolower($credentials['email']);
        $credentials['username'] = $this->createUsername($credentials['name']);
        $credentials['password'] = bcrypt($credentials['password']);

        $credentials['is_admin'] = false;
        $credentials['profile_photo_path'] = 'avatars/default.jpeg';

        $user = User::create($credentials);
        $token = $user->createToken($user->username);
        return response()->json([
            'status' => 201,
            'message' => 'User created successfully',
            'data' => [
                'token' => $token->plainTextToken,
                'user' => $user
            ],
        ]);
    }

    public function login()
    {
        $credentials = request(['email', 'password']);

        $user = User::where('email', $credentials['email'])->first();
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(
                [
                    'status' => 401,
                    'message' => 'Invalid credentials'
                ],
                401
            );
        }

        $user->tokens()->delete();
        $token = $user->createToken($user->username);
        return response()->json([
            'status' => 200,
            'message' => 'User logged in successfully',
            'data' => [
                'user' => $user,
                'token' => $token->plainTextToken,
            ]
        ]);
    }


    public function logout()
    {
        request()->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }

    private function createUsername($fullName)
    {
        $cleanName = strtolower(trim($fullName));
        $username = str_replace(' ', '', $cleanName);
        $username .= rand(1000, 9999);
        return $username;
    }
}
