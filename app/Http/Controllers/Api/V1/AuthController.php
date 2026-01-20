<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Authenticated;

#[Group('Authentication', 'API endpoints for user authentication')]
class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Login
     *
     * Authenticate a user and receive an API token.
     *
     * @unauthenticated
     */
    #[BodyParam('email', 'string', 'User email address', required: true, example: 'user@example.com')]
    #[BodyParam('password', 'string', 'User password', required: true, example: 'password123')]
    #[Response(['success' => true, 'message' => 'Login successful', 'data' => ['token' => 'your-api-token', 'user' => ['id' => 1, 'name' => 'John Doe', 'email' => 'user@example.com', 'role' => 'admin']]], 200, 'Successful login')]
    #[Response(['success' => false, 'message' => 'The provided credentials are incorrect.', 'errors' => ['email' => ['The provided credentials are incorrect.']]], 422, 'Invalid credentials')]
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ], 'Login successful');
    }

    /**
     * Logout
     *
     * Revoke the current API token and log out the user.
     */
    #[Authenticated]
    #[Response(['success' => true, 'message' => 'Logged out successfully', 'data' => null], 200, 'Successful logout')]
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logged out successfully');
    }

    /**
     * Get Current User
     *
     * Retrieve the authenticated user's profile information.
     */
    #[Authenticated]
    #[Response(['success' => true, 'data' => ['id' => 1, 'name' => 'John Doe', 'email' => 'user@example.com', 'role' => 'admin']], 200, 'User profile')]
    public function user(Request $request): JsonResponse
    {
        return $this->success([
            'id' => $request->user()->id,
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'role' => $request->user()->role,
        ]);
    }
}
