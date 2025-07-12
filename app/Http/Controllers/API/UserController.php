<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // تعديل صورة البروفايل فقط
    public function updateProfileImage(Request $request)
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $user = $request->user();

        // حفظ الصورة الجديدة
        $image = $request->file('profile_image');
        $imageName = uniqid() . '_' . time() . '.' . $image->getClientOriginalExtension(); // اسم فريد للصورة
        $path = $image->storeAs('profile_images', $imageName, 'public');

        // حذف الصورة القديمة لو كانت موجودة
        if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
            Storage::disk('public')->delete($user->profile_image);
        }

        // تحديث الصورة في قاعدة البيانات
        $user->profile_image = $path;
        $user->save();

        return response()->json([
            'message' => 'Profile image updated successfully.',
            'profile_image_url' => asset('storage/' . $path),
        ]);
    }
}
