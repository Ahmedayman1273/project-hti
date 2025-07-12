<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    // رفع إيصال الدفع
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048', // max 2MB
        ]);

        $user = $request->user();

        // حفظ الصورة في public/storage/payments
        $path = $request->file('image')->store('payments', 'public');

        // حفظ في قاعدة البيانات
        $payment = Payment::create([
            'user_id' => $user->id,
            'image_path' => $path,
        ]);

        return response()->json([
            'message' => 'Payment uploaded successfully',
            'payment' => $payment
        ]);
    }

    // عرض بيانات الدفع الخاصة بالمستخدم
    public function myPayment(Request $request)
    {
        $user = $request->user();

        $payment = Payment::where('user_id', $user->id)->latest()->first();

        if (!$payment) {
            return response()->json(['message' => 'No payment found'], 404);
        }

        return response()->json([
            'image_url' => asset('storage/' . $payment->image_path),
            'uploaded_at' => $payment->created_at,
        ]);
    }
}
