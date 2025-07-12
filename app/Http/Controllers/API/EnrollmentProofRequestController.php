<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EnrollmentProofRequest;

class EnrollmentProofRequestController extends Controller
{
    // إنشاء طلب إثبات قيد
    public function requestProof(Request $request)
    {
        $user = $request->user();

        // فقط الطلاب يمكنهم تقديم الطلب
        if ($user->type !== 'student') {
            return response()->json(['message' => 'Only students can request enrollment proof.'], 403);
        }

        // التأكد من عدم وجود طلب سابق قيد المعالجة
        $existingRequest = EnrollmentProofRequest::where('user_id', $user->id)
                                ->where('status', 'pending')
                                ->first();

        if ($existingRequest) {
            return response()->json(['message' => 'You already have a pending request.'], 400);
        }

        // إنشاء الطلب
        $requestRecord = EnrollmentProofRequest::create([
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Enrollment proof request submitted successfully.',
            'request' => $requestRecord
        ]);
    }

    // عرض حالة آخر طلب
    public function checkStatus(Request $request)
    {
        $user = $request->user();

        $latestRequest = EnrollmentProofRequest::where('user_id', $user->id)
                                ->latest()
                                ->first();

        if (!$latestRequest) {
            return response()->json(['message' => 'No request found.'], 404);
        }

        return response()->json([
            'status' => $latestRequest->status,
            'notes'  => $latestRequest->notes,
        ]);
    }
}
