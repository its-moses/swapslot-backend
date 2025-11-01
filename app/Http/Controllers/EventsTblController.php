<?php

namespace App\Http\Controllers;

use App\Models\AuthUser;
use App\Models\EventsTbl;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class EventsTblController extends Controller
{
    //
    public function insertEvent(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:150',
                'date' => 'required|date|after_or_equal:today',
                'startTime' => 'required|date_format:H:i',
                'endTime' => 'required|date_format:H:i|after:startTime',
                'status' => 'nullable|string'
            ]);

            // Create event
            $event = EventsTbl::create([
                'title' => $validated['title'],
                'event_date' => $validated['date'],
                'startTime' => $validated['startTime'],
                'endTime' => $validated['endTime'],
                'status' => $validated['status'] ?? 'BUSY',
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'type' => 'success',
                'message' => 'Event created successfully.',
                'data' => $event,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'type' => 'warning',
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function fetchEvents()
    {
        try {
            // ✅ Get logged-in user's events
            $events = EventsTbl::where('user_id', Auth::id())
                ->orderBy('startTime', 'asc')
                ->get();

            if ($events->isEmpty()) {
                return response()->json([
                    'type' => 'warning',
                    'msg' => 'No events found for this user.',
                    'data' => [],
                ], 200);
            }

            return response()->json([
                'type' => 'success',
                'msg' => 'Events fetched successfully.',
                'data' => $events,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'msg' => 'Failed to fetch events. ' . $e->getMessage(),
            ], 500);
        }
    }
    public function updateEventStatus(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|string|in:BUSY,SWAPPABLE',
            ]);

            $event = EventsTbl::findOrFail($id);
            $event->update(['status' => $validated['status']]);

            return response()->json([
                'type' => 'success',
                'msg' => 'Event status updated successfully.',
                'data' => $event,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'type' => 'warning',
                'msg' => 'Invalid status provided.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'msg' => 'Failed to update event status.',
            ], 500);
        }
    }
    public function fetchSwappableSlots()
    {
        try {
            $userId = Auth::id(); // Get logged-in user ID

            // ✅ Fetch all SWAPPABLE slots except current user’s
            $swappableEvents = EventsTbl::where('status', 'SWAPPABLE')
                ->where('user_id', '!=', $userId)
                ->orderBy('event_date', 'asc')
                ->get();

            if ($swappableEvents->isEmpty()) {
                return response()->json([
                    'type' => 'warning',
                    'msg' => 'No swappable slots found.',
                    'data' => [],
                ], 200);
            }

            return response()->json([
                'type' => 'success',
                'msg' => 'Swappable slots fetched successfully.',
                'data' => $swappableEvents,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'msg' => 'Failed to fetch swappable slots.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function fetchMySwappableSlots()
    {
        try {
            $userId = Auth::id(); // Logged-in user ID

            // ✅ Fetch user's own swappable slots
            $mySwappableEvents = EventsTbl::where('status', 'SWAPPABLE')
                ->where('user_id', $userId)
                ->orderBy('event_date', 'asc')
                ->get();

            if ($mySwappableEvents->isEmpty()) {
                return response()->json([
                    'type' => 'warning',
                    'msg' => 'You have no swappable slots.',
                    'data' => [],
                ], 200);
            }

            return response()->json([
                'type' => 'success',
                'msg' => 'Your swappable slots fetched successfully.',
                'data' => $mySwappableEvents,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'msg' => 'Failed to fetch your swappable slots.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
