<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\PasswordResetCode;

class PasswordResetController extends Controller
{
    // 1. إرسال كود إلى البريد الشخصي
    public function sendCode(Request $request)
    {
        \Log::info('📨 sendCode() triggered');

        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        \Log::info('👤 User: ' . $user->email . ' - Personal: ' . $user->personal_email);

        if (!$user || !$user->personal_email) {
            \Log::warning('❌ No personal email set for this user.');
            return response()->json(['message' => 'This user does not have a registered personal email'], 404);
        }

        $code = rand(1000, 9999);
        \Log::info("📟 Code generated: $code");

        PasswordResetCode::updateOrCreate(
            ['email' => $user->email],
            [
                'code' => bcrypt($code),
                'expires_at' => now()->addMinutes(10),
            ]
        );

        \Log::info("✅ Code saved to DB");

        try {
            Mail::raw("Your password reset code is: $code", function ($message) use ($user) {
                $message->to($user->personal_email)
                        ->subject('Password Reset Code');
            });

            \Log::info("✔ Mail Sent to: " . $user->personal_email . " — Code: $code");

            return response()->json(['message' => 'The code has been sent to your personal email']);

        } catch (\Exception $e) {
            \Log::error('❌ Mail Send Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to send email.'], 500);
        }
    }

    // 2. التحقق من الكود
    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code'  => 'required|string',
        ]);

        if (!$this->isValidCode($request->email, $request->code)) {
            \Log::warning("❌ Invalid or expired code attempt for {$request->email}");
            return response()->json(['message' => 'Invalid or expired code'], 422);
        }

        PasswordResetCode::where('email', $request->email)->delete();

        return response()->json(['message' => 'Code is valid']);
    }

    // 3. تغيير كلمة المرور
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'                 => 'required|email|exists:users,email',
            'code'                  => 'required|string',
            'password'              => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string|min:6',
        ]);

        if (!$this->isValidCode($request->email, $request->code)) {
            \Log::warning("❌ Reset password failed – invalid code for {$request->email}");
            return response()->json(['message' => 'Invalid or expired code'], 422);
        }

        User::where('email', $request->email)->update([
            'password' => Hash::make($request->password)
        ]);

        PasswordResetCode::where('email', $request->email)->delete();

        \Log::info("✔ Password reset successfully for {$request->email}");

        return response()->json(['message' => 'Password has been reset successfully']);
    }

    // دالة داخلية للتحقق من الكود
    private function isValidCode($email, $inputCode)
    {
        $record = PasswordResetCode::where('email', $email)->first();

        if (!$record || now()->gt($record->expires_at)) {
            return false;
        }

        return Hash::check($inputCode, $record->code);
    }
}
