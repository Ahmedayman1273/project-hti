<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Faq;

class FaqController extends Controller
{
    // إنشاء سؤال جديد
    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string',
            'answer'   => 'required|string',
        ]);

        $user = $request->user();
        if ($user->type !== 'admin') {
            return response()->json(['message' => 'Only admins can add FAQs.'], 403);
        }

        $faq = Faq::create([
            'question' => $request->question,
            'answer'   => $request->answer,
        ]);

        return response()->json([
            'message' => 'FAQ created successfully.',
            'faq' => $faq
        ]);
    }

    // عرض كل الأسئلة
    public function index()
    {
        $faqs = Faq::latest()->get();

        return response()->json([
            'faqs' => $faqs
        ]);
    }

    // تعديل سؤال
    public function update(Request $request, $id)
    {
        $faq = Faq::find($id);
        if (!$faq) {
            return response()->json(['message' => 'FAQ not found.'], 404);
        }

        $user = $request->user();
        if ($user->type !== 'admin') {
            return response()->json(['message' => 'Only admins can update FAQs.'], 403);
        }

        $request->validate([
            'question' => 'sometimes|required|string',
            'answer'   => 'sometimes|required|string',
        ]);

        $faq->update($request->only(['question', 'answer']));

        return response()->json([
            'message' => 'FAQ updated successfully.',
            'faq' => $faq
        ]);
    }

    // حذف سؤال
    public function destroy(Request $request, $id)
    {
        $faq = Faq::find($id);
        if (!$faq) {
            return response()->json(['message' => 'FAQ not found.'], 404);
        }

        $user = $request->user();
        if ($user->type !== 'admin') {
            return response()->json(['message' => 'Only admins can delete FAQs.'], 403);
        }

        $faq->delete();

        return response()->json(['message' => 'FAQ deleted successfully.']);
    }
}
