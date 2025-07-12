<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    // عرض كل الأحداث (متاح للجميع)
    public function index()
    {
        return response()->json(Event::orderBy('start_time', 'desc')->get());
    }

    // إضافة حدث (الأدمن فقط)
    public function store(Request $request)
    {
        if (auth()->user()->type !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'start_time'  => 'required|date',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('events', 'public');
        }

        $event = Event::create([
            'title'       => $request->title,
            'description' => $request->description,
            'start_time'  => $request->start_time,
            'image'       => $imagePath,
        ]);

        return response()->json($event, 201);
    }

    // تعديل حدث (الأدمن فقط)
    public function update(Request $request, Event $event)
    {
        if (auth()->user()->type !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title'       => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'start_time'  => 'sometimes|required|date',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }
            $event->image = $request->file('image')->store('events', 'public');
        }

        $event->update($request->only(['title', 'description', 'start_time']));

        return response()->json($event);
    }

    // حذف حدث (الأدمن فقط)
    public function destroy(Event $event)
    {
        if (auth()->user()->type !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($event->image) {
            Storage::disk('public')->delete($event->image);
        }

        $event->delete();
        return response()->json(['message' => 'Event deleted']);
    }
}
