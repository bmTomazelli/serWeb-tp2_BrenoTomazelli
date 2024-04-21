<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\HasApiTokens;
use HasApiTokens;

class AuthController extends Controller
{
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

        $user = new User();
        $user->login = $request->login;
        $user->password = bcrypt($request->password);
        $user->email = $request->email;
        $user->last_name = $request->last_name;
        $user->first_name = $request->first_name;
        $user->save();

        if(Auth::attempt($request->toArray())){
            $user=Auth::user();
            $token = $user->createToken('signInToken');
            return response()->json(['token'=>$token->plainTextToken])->setStatusCode(CREATED);
        }
        else{
            return response()->json(['message'=>'User not created'])->setStatusCode(SERVER_ERROR);
        }
    }

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

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'User successfully logged out'])->setStatusCode(NO_CONTENT);
    }
}
