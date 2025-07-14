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
    /**
     * @OA\Post(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="Register new user",
     *     description="Register a new user with username, password, and name",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "password",},
     *             @OA\Property(property="username", type="string", example="Nugrah"),
     *             @OA\Property(property="password", type="string", format="password", example="rahasia"),
     *             @OA\Property(property="name", type="string", example="Fabianugerah Bainashhiddiq")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Success register user",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="username", type="string", example="Nugrah"),
     *                 @OA\Property(property="name", type="string", example="Fabianugerah Bainashhiddiq")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="username", type="array",
     *                     @OA\Items(type="string", example="username must not be blank")
     *                 ),
     *                 @OA\Property(property="name", type="array",
     *                     @OA\Items(type="string", example="name must not be blank")
     *                 )
     *             )
     *         )
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

    /**
     * @OA\Post(
     *     path="/api/users/login",
     *     tags={"Users"},
     *     summary="Login user",
     *     description="Authenticate user and return token",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "password"},
     *             @OA\Property(property="username", type="string", example="Nugrah"),
     *             @OA\Property(property="password", type="string", format="password", example="rahasia")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Success login",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="username", type="string", example="Nugrah"),
     *                 @OA\Property(property="name", type="string", example="Fabianugerah Bainashhiddiq"),
     *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")
     *             )
     *         )
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

    /**
     * @OA\Get(
     *     path="/api/users/current",
     *     tags={"Users"},
     *     summary="Get current user",
     *     description="Return authenticated user's data",
     *     @OA\Response(
     *         response=200,
     *         description="Success get current user",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="username", type="string", example="Nugrah"),
     *                 @OA\Property(property="name", type="string", example="Fabianugerah Bainashhiddiq")
     *             )
     *         )
     *     )
     * )
     */
    public function get(Request $request): UserResource
    {
        $user = Auth::user();
        return new UserResource($user);
    }

    /**
     * @OA\Patch(
     *     path="/api/users/current",
     *     tags={"Users"},
     *     summary="Update current user",
     *     description="Update authenticated user's name and/or password",
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="username", type="string", example="Fabian"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success update user",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="username", type="string", example="Fabian"),
     *                 @OA\Property(property="name", type="string", example="Fabianugerah Bainashhiddiq")
     *             )
     *         )
     *     )
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

    /**
     * @OA\Delete(
     *     path="/api/users/logout",
     *     tags={"Users"},
     *     summary="Logout current user",
     *     description="Invalidate the current user's token and logout",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Success logout user",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="boolean", example=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
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
