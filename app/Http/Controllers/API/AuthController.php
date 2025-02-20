<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @group Auth Management
 *
 * APIs for handling user login
 */

class AuthController extends Controller
{
    /**
     * Login API for users.
     *
     * @group Auth
     * @authenticated
     *
     * @bodyParam email string required The recipient's user ID. Example: abc123@gmail.com
     * @bodyParam password string required Password of user. Example: abc123
     *
     * @response 200 {
     *   "token": "1|jdQVmNFRryM4tUbYt8TyGnJaHMC2SrEdO0taIQIO3ed0b484",
     *   "user": {
            "id": 1,
            "name": "John Doe",
            "email": "johndoe@gmail.com",
            "role": "admin",
            "balance": "5000.00",
            "email_verified_at": "2025-02-20T09:08:00.000000Z",
            "created_at": "2025-02-20T09:08:00.000000Z",
            "updated_at": "2025-02-20T09:08:00.000000Z"
         }
     * }
     * @response 401 {
     *   "message": "Invalid credentials",
     * }
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }
}
