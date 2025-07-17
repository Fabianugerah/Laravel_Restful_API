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
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

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
     *             required={"username", "name", "password"},
     *             @OA\Property(property="username", type="string", example="Nugrah"),
     *             @OA\Property(property="name", type="string", example="Fabianugerah Bainasshiddiq"),
     *             @OA\Property(property="password", type="string", example="rahasia123")
     *
     *         )
     *     ),
     *     @OA\Response(response=201, description="Success register user")
     * )
     */
    public function register(UserRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (User::where('username', $data['username'])->exists()) {
            throw new HttpResponseException(response([
                "errors" => [
                    "username" => ["username already registered"]
                ]
            ], 400));
        }

        $user = User::create([
            'username' => $data['username'],
            'name'     => $data['name'],
            'password' => Hash::make($data['password'])

        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => new UserResource($user),
        ], 201);
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
     *             @OA\Property(property="username", type="string", example="Nugrah"),
     *             @OA\Property(property="password", type="string", example="rahasia123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Success login")
     * )
     */
    public function login(UserLoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'username or password wrong'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could not create token'], 500);
        }

        $user = auth()->user();
        $loggedUser = User::find($user->id);
        if ($loggedUser) {
            $loggedUser->token = $token;
            $loggedUser->save();
        }

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token
        ]);
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
        return new UserResource(auth()->user());
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
     *             @OA\Property(property="name", type="string", example="Fabianugerah"),
     *             @OA\Property(property="password", type="string", example="rahasia")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Success update user")
     * )
     */
    public function update(UserUpdateRequest $request): UserResource
    {
        $data = $request->validated();
        $user = auth()->user();

        $updateUser = User::find($user->id);
        if ($updateUser) {
            if (isset($data['name'])) {
                $user->name = $data['name'];
            }

            if (isset($data['password'])) {
                $user->password = Hash::make($data['password']);
            }
            $updateUser->save();
        }
        return new UserResource($user);
    }

    // LOGOUT CURRENT USER
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
        auth()->logout();
        return response()->json(["message" => "Successfully logged out"], 200);
    }
}
