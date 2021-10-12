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
            ], [
                'email.required' => 'Campo E-mail é obrigatório',
                'email.email' => 'Campo E-mail deve ser um e-mail válido',
                'password.required' => 'Campo Senha é obrigatório',
                'password.min' => 'Campo Senha deve ter no mínimo 6 caracteres',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $token_validity = 24 * 60; //1 day

            $this->guard()->factory()->setTTL($token_validity);
            $token = $this->guard()->attempt($validator->validated());

            if (!$token) {
                return response()->json(['error' => 'Email or password incorrect'], 401);
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
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6|confirmed'
            ], [
                'name.required' => 'Campo Nome completo é obrigatório',
                'email.unique' => 'Este E-mail já está sendo utilizando',
                'email.required' => 'Campo E-mail é obrigatório',
                'email.email' => 'Campo E-mail deve ser um e-mail válido',
                'name.between' => 'Campo Nome completo deve ter no mínimo 2 e no máximo 100 caracteres',
                'password.required' => 'Campo Senha é obrigatório',
                'password.min' => 'Campo Senha deve ter no mínimo 6 caracteres',
                'password.confirmed' => 'Campo Senha deve ser igual ao campo password_confirmation',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
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
