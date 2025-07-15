<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StudentRequest;
use Illuminate\Support\Facades\Storage;

class StudentRequestController extends Controller
{
    // جلب كل الطلبات الخاصة بالمستخدم الحالي
    public function index(Request $request)
    {
        $user = $request->user();

        $requests = StudentRequest::where('user_id', $user->id)
            ->with('requestType')
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'requests' => $requests
        ]);
    }

    // إنشاء طلب جديد
    public function store(Request $request)
    {
        \Log::info('🧪 Incoming request:', $request->all());

        if ($request->hasFile('receipt_image')) {
            \Log::info('🖼 Receipt image received');
        } else {
            \Log::warning('⚠️ Receipt image MISSING');
        }

        $request->validate([
            'request_id'       => 'required|exists:requests,id',
            'count'            => 'required|integer|min:1',
            'student_name_ar'  => 'required|string|max:255',
            'student_name_en'  => 'required|string|max:255',
            'department'       => 'required|string|max:255',
            'receipt_image'    => 'required|image|max:2048',
        ]);

        // ✅ تحقق من وجود طلب سابق بنفس النوع والحالة pending
        $existing = StudentRequest::where('user_id', $request->user()->id)
            ->where('request_id', $request->request_id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'You already submitted this request and it is still pending.'
            ], 409); // 409 Conflict
        }

        $imagePath = $request->file('receipt_image')->store('receipts', 'public');

        $requestType = \App\Models\Request::find($request->request_id);
        $totalPrice = $requestType->price * $request->count;

        $studentRequest = StudentRequest::create([
            'user_id'         => $request->user()->id,
            'request_id'      => $request->request_id,
            'count'           => $request->count,
            'total_price'     => $totalPrice,
            'receipt_image'   => $imagePath,
            'student_name_ar' => $request->student_name_ar,
            'student_name_en' => $request->student_name_en,
            'department'      => $request->department,
            'status'          => 'pending',
        ]);

        return response()->json([
            'message' => 'Request submitted successfully',
            'request' => $studentRequest
        ], 201);
    }

    // حذف الطلب (لو لسه pending فقط)
    public function destroy(Request $request, $id)
    {
        $studentRequest = StudentRequest::find($id);

        if (!$studentRequest || $studentRequest->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Not found or unauthorized'], 404);
        }

        if ($studentRequest->status !== 'pending') {
            return response()->json(['message' => 'Cannot delete approved/rejected requests'], 403);
        }

        if ($studentRequest->receipt_image) {
            Storage::disk('public')->delete($studentRequest->receipt_image);
        }

        $studentRequest->delete();

        return response()->json(['message' => 'Request deleted successfully']);
    }
}
