<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
    
class UserController extends Controller
{
    // REGISTER USER
    /**
     * @OA\Post(
     *     path="/api/users",
     *     summary="Register new user",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "password", "name"},
     *             @OA\Property(property="username", type="string"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="name", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Success register user"
     *     )
     * )
     */
    public function register(UserRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (User::where('username', $data['username'])->count() == 1) {
            throw new HttpResponseException(response([
                "errors" => [
                    "username" => [
                        "username already registered"
                    ]
                ]
            ], 400));
        }

        $user = new User($data);
        $user->password = Hash::make($data['password']);
        if ($user instanceof \App\Models\User) {
            $user->save();
        }

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    // LOGIN USER
    /**
     * @OA\Post(
     *     path="/api/users/login",
     *     summary="Login user",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "password"},
     *             @OA\Property(property="username", type="string"),
     *             @OA\Property(property="password", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success login"
     *     )
     * )
     */
    public function login(UserLoginRequest $request): UserResource
    {
        $data = $request->validated();

        $user = User::where('username', $data['username'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw new HttpResponseException(response([
                "errors" => [
                    "message" => [
                        "username or password wrong"
                    ]
                ]
            ], 401));
        }

        $user->token = Str::uuid()->toString();
        if ($user instanceof \App\Models\User) {
            $user->save();
        }
        return new UserResource($user);
    }

    // GET CURRENT USER
    /**
     * @OA\Get(
     *     path="/api/users/current",
     *     summary="Get current user",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Success get current user")
     * )
     */
    public function get(Request $request): UserResource
    {
        $user = Auth::user();
        return new UserResource($user);
    }

    // UPDATE CURRENT USER
    /**
     * @OA\Patch(
     *     path="/api/users/current",
     *     summary="Update current user",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="password", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Success update user")
     * )
     */
    public function update(UserUpdateRequest $request): UserResource
    {
        $data = $request->validated();
        $user = Auth::user();

        if (isset($data['name'])) {
            $user->name = $data['name'];
        }

        if (isset($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        if ($user instanceof \App\Models\User) {
            $user->save();
        }
        return new UserResource($user);
    }

    // LOGOUT USER
    /**
     * @OA\Delete(
     *     path="/api/users/logout",
     *     summary="Logout current user",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Success logout user")
     * )
     */

    public function logout(Request $request): JsonResponse
    {
        $user = Auth::user();
        if ($user instanceof \App\Models\User) {
            $user->token = null;
            $user->save();
        }

        return response()->json([
            "data" => true
        ])->setStatusCode(200);
    }
}
