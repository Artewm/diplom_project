<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Illuminate\Support\Facades\Log;


class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Middleware теперь определено в маршрутах
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);
        Log::info('Login attempt with credentials: ' . json_encode($credentials));

        try {
            if (! $token = auth()->attempt($credentials)) {
                Log::warning('Unauthorized login attempt for email: ' . $credentials['email']);
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            Log::info('Login successful for user: ' . $credentials['email']);
            return $this->respondWithToken($token);
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Get a JWT via given credentials using GET method (for testing).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /*
    public function loginGet(Request $request)
    {
        $credentials = $request->only(['email', 'password']);
        Log::info('LoginGet attempt with credentials: ' . json_encode($credentials));
        
        try {
            if (! $token = auth()->attempt($credentials)) {
                Log::warning('Unauthorized login attempt for email: ' . $credentials['email']);
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            Log::info('Login successful for user: ' . $credentials['email']);
            return $this->respondWithToken($token);
        } catch (\Exception $e) {
            Log::error('LoginGet error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
    */

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
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

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}