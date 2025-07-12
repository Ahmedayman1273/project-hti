<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Payment;
use App\Models\EnrollmentProofRequest;
use App\Models\CertificateRequest;

class AdminUserController extends Controller
{
    // إنشاء مستخدم جديد (طالب أو خريج)
    public function createUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'phone_number' => 'required|string|max:20',
            'type'         => 'required|in:student,graduate',
            'password'     => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name'         => $request->name,
            'email'        => $request->email,
            'phone_number' => $request->phone_number,
            'type'         => $request->type,
            'password'     => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'User created successfully.',
            'user'    => $user
        ], 201);
    }

    // عرض كل إيصالات الدفع
    public function listPayments()
    {
        $payments = Payment::with('user:id,name,email')->latest()->get();

        return response()->json([
            'payments' => $payments
        ]);
    }

    // عرض إيصال دفع لطالب محدد
    public function showPayment($id)
    {
        $payment = Payment::with('user:id,name,email')->find($id);

        if (!$payment) {
            return response()->json(['message' => 'Payment not found.'], 404);
        }

        return response()->json([
            'id'         => $payment->id,
            'status'     => $payment->status,
            'notes'      => $payment->notes,
            'image_url'  => asset('storage/' . $payment->image_path),
            'user'       => $payment->user,
            'created_at' => $payment->created_at,
        ]);
    }

    // عرض جميع طلبات إثبات القيد
    public function listEnrollmentRequests()
    {
        $requests = EnrollmentProofRequest::with('user:id,name,email')->latest()->get();

        return response()->json([
            'requests' => $requests
        ]);
    }

    // تحديث حالة طلب إثبات القيد
    public function updateEnrollmentRequestStatus(Request $request, $id)
    {
        $request->merge([
            'status' => strtolower(trim($request->status)),
        ]);

        $request->validate([
            'status' => 'required|in:approved,rejected',
            'notes'  => 'nullable|string',
        ]);

        $record = EnrollmentProofRequest::find($id);

        if (!$record) {
            return response()->json(['message' => 'Request not found.'], 404);
        }

        $record->update([
            'status' => $request->status,
            'notes'  => $request->notes,
        ]);

        return response()->json(['message' => 'Request updated successfully.']);
    }

    // عرض جميع طلبات شهادة التخرج
    public function listCertificateRequests()
    {
        $requests = CertificateRequest::with('user:id,name,email')->latest()->get();

        return response()->json([
            'requests' => $requests
        ]);
    }

    // تحديث حالة طلب شهادة التخرج
    public function updateCertificateRequestStatus(Request $request, $id)
    {
        $request->merge([
            'status' => strtolower(trim($request->status)),
        ]);

        $request->validate([
            'status' => 'required|in:approved,rejected',
            'notes'  => 'nullable|string',
        ]);

        $record = CertificateRequest::find($id);

        if (!$record) {
            return response()->json(['message' => 'Request not found.'], 404);
        }

        $record->update([
            'status' => $request->status,
            'notes'  => $request->notes,
        ]);

        return response()->json(['message' => 'Request updated successfully.']);
    }

    // إنشاء أدمن جديد
    public function createAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $admin = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'type'     => 'admin',
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Admin created successfully.',
            'admin'   => $admin
        ], 201);
    }

    // حذف أدمن مع منع حذف النفس
    public function deleteAdmin($id)
    {
        $admin = User::where('type', 'admin')->find($id);

        if (!$admin) {
            return response()->json(['message' => 'Admin not found.'], 404);
        }

        if (auth()->id() == $admin->id) {
            return response()->json(['message' => 'You cannot delete yourself.'], 403);
        }

        $admin->delete();

        return response()->json(['message' => 'Admin deleted successfully.']);
    }
}
