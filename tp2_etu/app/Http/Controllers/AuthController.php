<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="The name of the user",
 *         example="John Doe"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         description="The email of the user",
 *         example="john.doe@example.com"
 *     ),
 */

   /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Auth"},
     *     summary="Register a new user",
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name","email","password"},
     *              @OA\Property(property="name", type="string", format="text", example="John Doe"),
     *              @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
     *              @OA\Property(property="password", type="string", format="password", example="Passw0rd"),
     *          ),
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="user", type="object"),
     *              @OA\Property(property="message", type="string", example="User registered successfully"),
     *          )
     *     ),
     *     @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *     ),
     *     @OA\Response(
     *          response=401,
     *          description="Unauthorized",
     *     ),
     * )
    */
    public function register(Request $request)
    {
        $request->validate([
            'login' => 'required|string|max:50',
            'password' => 'required|string|max:255',
            'email' => 'required|string|email|max:50|unique:users',
            'last_name' => 'required|string|max:50',
            'first_name' => 'required|string|max:50',
        ]);

        //Une fois validé, créer et enregistrer le User dans la BD avec les infos de la requête (encryptez le mot de passe avec bcrypt !)

        $user = User::create([
            'login' => $request->login,
            'password' => bcrypt($request->password),
            'email' => $request->email,
            'last_name' => $request->last_name,
            'first_name' => $request->first_name,
        ]);

        $token = $user->createToken('Personal Access Token')->plainTextToken;
        return response()->json(['message' => 'User successfully registered', 'token' => $token])->setStatusCode(CREATED);
    }
    /**
 * @OA\Post(
 *     path="/api/login",
 *     tags={"Auth"},
 *     summary="Login user",
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              required={"email","password"},
 *              @OA\Property(property="email", type="string", format="email", example="john_doe@example.com"),
 *              @OA\Property(property="password", type="string", format="password", example="Passw0rd!"),
 *          ),
 *     ),
 *     @OA\Response(
 *          response=200,
 *          description="User successfully logged in",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="User successfully logged in"),
 *              @OA\Property(property="token", type="string", example="2|Pznhz8nZXdkHyPvC5sKHaGgaRYk8zN1c5fK3XrSL"),
 *          )
 *     ),
 *     @OA\Response(
 *          response=422,
 *          description="Validation Error",
 *     )
 * )
 */

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('Personal Access Token')->plainTextToken;

        return response()->json(['message' => 'User successfully logged in', 'token' => $token]);
    }

    /**
     * @OA\Post(
     *    path="/api/logout",
     *   tags={"Auth"},
     *  summary="Logout user",
     * @OA\Response(
     *     response=204,
     *   description="User successfully logged out",
     * )
     * )
     */

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'User successfully logged out'])->setStatusCode(NO_CONTENT);
    }
}
