<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            Log::error('Error al validar los datos de registro');
            return response()->json($validator->errors(), 400);
        }

        $user = \App\Models\User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));

        Log::info('Usuario creado correctamente');
        return response()->json(['message' => 'User created successfully', 'user' => $user]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            Log::error('Error al autenticar el usuario');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Genera un segundo token (id_token) o usa el mismo token para simplificación
        $idToken = $this->generateIdToken();

        Log::info('Usuario autenticado correctamente');
        return $this->respondWithToken($token, $idToken);
    }
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userinfo(){
        Log::info('Se ha consultado User info');
        return response()->json(auth()->user());
    }
    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        Log::info('Usuario deslogueado correctamente');
        return response()->json(['message' => 'User successfully logged out']);
    }
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(){

        $token = auth()->refresh();
        $idToken = $this->generateIdToken();

        return $this->respondWithToken($token, $idToken);
    }
    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $idToken){

        Log::info('Token generado correctamente');
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'id_token' => $idToken, // Incluye el id_token en la respuesta
        ]);
    }

    protected function generateIdToken()
    {
        Log::info('Generando id_token');
        //generar un nuevo JWT, o simplemente devolver el mismo token por simplicidad
        return auth()->claims(['sub' => auth()->user()->id])->tokenById(auth()->user()->id);
    }
}


