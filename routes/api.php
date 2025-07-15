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


use App\Http\Controllers\API\StudentRequestController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/student-requests', [StudentRequestController::class, 'index']);
    Route::post('/student-requests', [StudentRequestController::class, 'store']);
    Route::delete('/student-requests/{id}', [StudentRequestController::class, 'destroy']);
});


use App\Http\Controllers\API\RequestController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/requests', [RequestController::class, 'index']);
    Route::post('/requests', [RequestController::class, 'store']);
    Route::put('/requests/{id}', [RequestController::class, 'update']);
    Route::delete('/requests/{id}', [RequestController::class, 'destroy']);
});


Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/admin/users', [AdminUserController::class, 'createUser']);
    Route::patch('/admin/users/{id}/type', [AdminUserController::class, 'changeUserType']);
    Route::post('/admin/import-users', [AdminUserController::class, 'importUsersFromExcel']);

    Route::get('/admin/student-requests', [AdminUserController::class, 'allStudentRequests']);
    Route::patch('/admin/student-requests/{id}/accept', [AdminUserController::class, 'acceptStudentRequest']);
    Route::patch('/admin/student-requests/{id}/reject', [AdminUserController::class, 'rejectStudentRequest']);
});


Route::middleware('auth:api')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
});



