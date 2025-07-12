<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CertificateRequest;

class CertificateRequestController extends Controller
{
    // تقديم طلب شهادة تخرج
    public function requestCertificate(Request $request)
    {
        $user = $request->user();

        if ($user->type !== 'graduate') {
            return response()->json([
                'message' => 'Only graduates can request certificates.'
            ], 403);
        }

        $existing = CertificateRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'You already have a pending certificate request.'
            ], 400);
        }

        $requestRecord = CertificateRequest::create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Certificate request submitted successfully.',
            'request' => $requestRecord
        ]);
    }

    // عرض حالة الطلب
    public function checkStatus(Request $request)
    {
        $user = $request->user();

        if ($user->type !== 'graduate') {
            return response()->json([
                'message' => 'Only graduates can view certificate request status.'
            ], 403);
        }

        $requestRecord = CertificateRequest::where('user_id', $user->id)->latest()->first();

        if (!$requestRecord) {
            return response()->json([
                'message' => 'No certificate request found.'
            ], 404);
        }

        return response()->json([
            'status' => $requestRecord->status,
            'notes' => $requestRecord->notes
        ]);
    }
}
