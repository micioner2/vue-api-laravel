<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8'
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password'])
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer'
        ], 200);
    }

    public function login(Request $request)
    {
        // if (!Auth::attempt($request->only('email', 'password'))) {
        //     return response()->json([
        //         'message' => 'Invalido'
        //     ], 401);
        // }

        $user = User::where('email', $request->email)->first();

        if(isset($user->id)){
            if(Hash::check($request->password, $user->password)){
                $token = $user->createToken('auth_token')->plainTextToken;
                return response()->json([
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ], 200);
            }else{
                return response()->json([
                    'msg' => 'La password es incorrecta'
                ],401);
            }
        }else{
            return response()->json([
                'msg' => 'El usuario no existe'
            ], 401);
        }



    }

    public function infoUser(Request $request)
    {
        // return $request->user();
        $user = $request->user();
        return DB::select("SELECT us.id,us.name, us.created_at AS usuario_creado, tok.created_at AS inicio_sesion, tok.last_used_at AS utlima_sesion
        FROM users AS us INNER JOIN personal_access_tokens AS tok ON us.id = tok.tokenable_id
        WHERE us.id =".$user->id);
    }

    public function logout(Request $request)
    {
        // este metodo elimina todo los tokens del usuario
        // $request->user()->tokens->each(function ($token, $key) {
        //     $token->delete();
        // });

        // este metodo elimina solo el token que pertenece al token enviado del localstorage
        $request->user()->currentAccessToken()->delete();

        return response(null, 204);
    }

}
