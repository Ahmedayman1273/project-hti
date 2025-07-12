<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // تسجيل الدخول
    public function login(Request $request)
    {
        try {
            \Log::info('== LOGIN STARTED ==');

            $credentials = $request->validate([
                'email'    => ['required', 'email'],
                'password' => ['required'],
            ]);

            \Log::info('Validated:', $credentials);

            if (!Auth::attempt($credentials)) {
                \Log::warning('Invalid credentials for ' . $credentials['email']);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials'
                ], 401);
            }

            $user = Auth::user();

            if (!$user) {
                \Log::error('Auth passed but user is null');
                return response()->json(['message' => 'Unexpected auth error'], 500);
            }

            \Log::info('User found: ' . $user->email . ' - type: ' . $user->type);

            if (
                in_array($user->type, ['student', 'graduate']) &&
                (
                    $this->isWeb($request) || // لو الطلب من الويب
                    !$request->expectsJson()
                )
            ) {
                \Log::warning('Access denied for user: ' . $user->email);
                Auth::logout();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Access denied from web'
                ], 403);
            }

            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

          return response()->json([
    'status' => 'success',
    'message' => 'Login successful',
    'token' => $token,
    'user'  => [
        'id'               => $user->id,
        'name'             => $user->name,
        'email'            => $user->email,
        'personal_email'   => $user->personal_email,
        'type'             => $user->type,
        'major'            => $user->major,
        'phone_number'     => $user->phone_number,
        'profile_photo'    => $user->profile_photo_path
                                ? asset('storage/' . $user->profile_photo_path)
                                : asset('images/default_avatar.png'),
        'created_at'       => $user->created_at,
        'updated_at'       => $user->updated_at,
    ]
]);

        } catch (\Throwable $e) {
            \Log::error('LOGIN EXCEPTION: ' . $e->getMessage());
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    // تسجيل الخروج
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ]);
    }

    // تحديد إذا كان الطلب من الويب
    private function isWeb(Request $request): bool
{
    return strtolower($request->header('X-From')) === 'web';
}

}
