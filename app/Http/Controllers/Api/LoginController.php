<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    //
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'email|required',
                'password' => 'required'
            ]);

            $credentials = request(['email', 'password']);

            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'status_code' => 500,
                    'message' => 'Unauthorized'
                ]);
            }

            $user = User::where('email', $request->email)->first();

            if (!Hash::check($request->password, $user->password, [])) {
                return response()->json([
                    'status_code' => 403,
                    'message' => "Login fails"
                ]);
            }

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'status_code' => 200,
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
            ]);
        } catch (\Exception $error) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Error in Login',
                'error' => $error,
            ]);
        }
    }

    public function register(RegisterRequest $request)
    {
        try {
            $email  = $request->email;
            $password = Hash::make($request->password);
            $fullname = $request->fullname;
            $role = ROLE_USER;

            $dataCreate = [
                'email' => $email,
                'password' => $password,
                'role' => $role,
                'fullname' => $fullname,
                'created_at' => Carbon::now(),
            ];

            $user = User::create($dataCreate);


            return response()->json([
                'status' => Response::HTTP_OK,
                'data' => $user,
            ], Response::HTTP_OK);
        } catch (\Exception $error) {
            return response()->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'error' => $error,
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
