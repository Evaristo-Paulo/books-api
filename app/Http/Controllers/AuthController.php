<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|min:6'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $token_validity = 24 * 60; //1 day

            $this->guard()->factory()->setTTL($token_validity);

            if ($token = $this->guard()->attempt($validator->validated())) {
                return response()->json(['error' => 'Email or pasword incorrect'], 401);
            }

            return $this->respondWithToken($token);
        } catch (\Exception $e) {
            return response()->json(['error' => $e], 400);
        }
    }
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|between:2,100',
                'email' => 'required!email|unique:users,email',
                'password' => 'required|min:6'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $user = [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
            ];

            $saved_user = User::create($user);

            return response()->json([
                'message' => 'User created successfully',
                'user' => $saved_user
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e], 400);
        }
    }
    public function profile()
    {
        try {
            return response()->json(['user' => $this->guard()->user()], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e], 400);
        }
    }
    public function logout()
    {
        $this->guard()->logout();
        return response()->json(['messsage' => 'User logged successfully'], 200);
    }
    public function refresh()
    {
        try {
            return $this->respondWithToken($this->guard()->refresh());
        } catch (\Exception $e) {
            return response()->json(['error' => $e], 400);
        }
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'token_validity' => $this->guard()->factory()->getTTL() * 60,
        ]);
    }
    protected function guard()
    {
        return Auth::guard();
    }
}
