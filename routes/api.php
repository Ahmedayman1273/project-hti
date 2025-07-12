<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Controllers
use App\Http\Controllers\API\{
    AuthController,
    PasswordResetController,
    EnrollmentProofRequestController,
    CertificateRequestController,
    NewsController,
    EventController,
    FaqController,
    UserController,
    AdminUserController,
    PaymentController,
    ProfileController
};


// ✅ [ عام - يحصل على بيانات المستخدم المسجل ]
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// ✅ [ تسجيل الدخول والخروج ]
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


// ✅ [ إعادة تعيين كلمة المرور ]
Route::prefix('password')->middleware('throttle:5,1')->group(function () {
    Route::post('/send-code', [PasswordResetController::class, 'sendCode'])->middleware('throttle:3,1');
    Route::post('/verify-code', [PasswordResetController::class, 'verifyCode']);
    Route::post('/reset', [PasswordResetController::class, 'resetPassword']);
});


// ✅ [ إثبات القيد ]
Route::middleware('auth:sanctum')->prefix('enrollment-proof')->group(function () {
    Route::post('/request', [EnrollmentProofRequestController::class, 'requestProof']);
    Route::get('/status', [EnrollmentProofRequestController::class, 'checkStatus']);
});


// ✅ [ شهادة التخرج ]
Route::middleware('auth:sanctum')->prefix('certificate')->group(function () {
    Route::post('/request', [CertificateRequestController::class, 'requestCertificate']);
    Route::get('/status', [CertificateRequestController::class, 'checkStatus']);
});


// ✅ [ الأخبار News ]
Route::middleware('auth:sanctum')->prefix('news')->group(function () {
    Route::get('/', [NewsController::class, 'index']);
    Route::post('/', [NewsController::class, 'store']);
    Route::put('/{news}', [NewsController::class, 'update']);
    Route::delete('/{news}', [NewsController::class, 'destroy']);
});


// ✅ [ الأحداث Events ]
Route::middleware('auth:sanctum')->prefix('events')->group(function () {
    Route::get('/', [EventController::class, 'index']);
    Route::post('/', [EventController::class, 'store']);
    Route::put('/{event}', [EventController::class, 'update']);
    Route::delete('/{event}', [EventController::class, 'destroy']);
});


// ✅ [ الأسئلة الشائعة FAQs ]
Route::middleware('auth:sanctum')->prefix('faqs')->group(function () {
    Route::get('/', [FaqController::class, 'index']);
    Route::post('/', [FaqController::class, 'store']);
    Route::put('/{id}', [FaqController::class, 'update']);
    Route::delete('/{id}', [FaqController::class, 'destroy']);
});


// ✅ [ المستخدم User ]
Route::middleware('auth:sanctum')->group(function () {
    Route::put('/user/update-profile-image', [UserController::class, 'updateProfileImage']);
});


// ✅ [ الملف الشخصي Profile ]
Route::middleware('auth:sanctum')->prefix('profile')->group(function () {
    Route::get('/', [ProfileController::class, 'profile']);
    Route::post('/photo', [ProfileController::class, 'uploadPhoto']);
    Route::delete('/photo', [ProfileController::class, 'deletePhoto']);
});


// ✅ [ الدفع Payments ]
Route::middleware('auth:sanctum')->prefix('payments')->group(function () {
    Route::post('/upload', [PaymentController::class, 'upload']);
    Route::get('/mine', [PaymentController::class, 'myPayment']);
});


// ✅ [ لوحة تحكم الأدمن Admin Panel ]
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {

    // المستخدمين
    Route::post('/create-user', [AdminUserController::class, 'createUser']);

    // الأدمنز
    Route::post('/create-admin', [AdminUserController::class, 'createAdmin']);
    Route::delete('/delete-admin/{id}', [AdminUserController::class, 'deleteAdmin']);

    // الدفع
    Route::get('/payments', [AdminUserController::class, 'listPayments']);
    Route::get('/payments/{id}', [AdminUserController::class, 'showPayment']);

    // إثبات القيد
    Route::get('/enrollment-requests', [AdminUserController::class, 'listEnrollmentRequests']);
    Route::put('/enrollment-requests/{id}', [AdminUserController::class, 'updateEnrollmentRequestStatus']);

    // شهادات التخرج
    Route::get('/certificate-requests', [AdminUserController::class, 'listCertificateRequests']);
    Route::put('/certificate-requests/{id}', [AdminUserController::class, 'updateCertificateRequestStatus']);
});
