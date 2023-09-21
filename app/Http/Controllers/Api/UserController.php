<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $users = User::get();

        return response()->json([
            'status' => HttpResponse::HTTP_OK,
            'data' => $users
        ],HttpResponse::HTTP_OK);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserRequest $request)
    {
        //
        try {
            //code..
            
           User::create([
            'role' =>$request->role,
            'password' => Hash::make($request->password),
            'fullname' => $request->fullname,
            'email' => $request->email,
            'avatar' => $request->avatar ?? "",
           ]);
            return response()->json([
                'status' => HttpResponse::HTTP_OK,
                'message' => 'Tạo tài khoản thành công',
            ],HttpResponse::HTTP_OK);

        } catch (\Exception $error) {
            return response()->json([
                'status' => HttpResponse::HTTP_BAD_REQUEST,
                'message' => 'Tạo tài khoản thất bại',
                'error' => $error,
            ],HttpResponse::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $user = User::findOrFail($id);
        $userResource = new UserResource($user);

        return response()->json([
            'status' => HttpResponse::HTTP_OK,
            'data' => $userResource,
        ],HttpResponse::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'fullname' => 'required|string',
            'email' => ['required','email',Rule::unique('users','email')->ignore($user->id)],
            'role' => 'required',
        ], [
            'fullname.required' => 'The name field is required.',
            'email.required' => 'The email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'The email address is already in use.',
            'role.required' => 'The role field is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            //code...
            $user->update( $request->all());

            return response()->json([
                'status' => HttpResponse::HTTP_OK,
                'message' => 'Update User successfull',
            ]);
        } catch (\Exception $error) {
            //throw $th;
            return response()->json([
                'status' =>HttpResponse::HTTP_BAD_REQUEST,
                'message' => $error
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        try{
            $user = User::findOrFail($id);
            $user->delete();
    
            return response()->json([
                'status' => HttpResponse::HTTP_OK,
                'message' => 'Delete User Successfull',
            ],HttpResponse::HTTP_OK);
        } catch(\Exception $error) {
            return response()->json([
                'status' => HttpResponse::HTTP_BAD_REQUEST,
                'message' => 'Delete User Successfull',
                'error' => $error,
            ],HttpResponse::HTTP_BAD_REQUEST);
        }
    }

    public function getProfile($id) {
        try {
            
            $user = User::findOrFail($id);

            return response()->json([
                'status' => HttpResponse::HTTP_OK,
                'data' => $user,
            ],HttpResponse::HTTP_OK);

        } catch (\Exception $error) {
            return response()->json([
                'status' => HttpResponse::HTTP_BAD_REQUEST,
                'error' => $error
            ],HttpResponse::HTTP_BAD_REQUEST);
        }
    }
}
