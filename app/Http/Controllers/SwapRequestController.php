<?php

namespace App\Http\Controllers;

use App\Models\EventsTbl;
use App\Models\SwapRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SwapRequestController extends Controller
{
    //
    public function createSwapRequest(Request $request)
    {
        try {
            // ✅ Validate input
            $validated = $request->validate([
                'mySlotId' => 'required|integer|exists:eventstbl,id',
                'theirSlotId' => 'required|integer|different:mySlotId|exists:eventstbl,id',
            ]);

            $userId = Auth::id();

            // ✅ Fetch both slots
            $mySlot = EventsTbl::find($validated['mySlotId']);
            $theirSlot = EventsTbl::find($validated['theirSlotId']);

            // ❌ Validate both slots are SWAPPABLE
            if ($mySlot->status !== 'SWAPPABLE' || $theirSlot->status !== 'SWAPPABLE') {
                return response()->json([
                    'type' => 'warning',
                    'msg' => 'Both slots must be SWAPPABLE to initiate a swap request.',
                ], 400);
            }

            // ❌ Ensure the logged-in user owns the "mySlot"
            if ($mySlot->user_id !== $userId) {
                return response()->json([
                    'type' => 'warning',
                    'msg' => 'You can only request a swap for your own slot.',
                ], 403);
            }

            // ✅ Create new swap request
            $swapRequest = SwapRequest::create([
                'mySlotId' => $validated['mySlotId'],
                'theirSlotId' => $validated['theirSlotId'],
                'requester_user_id' => $userId,
                'receiver_user_id' => $theirSlot->user_id,
                'status' => 'PENDING',
            ]);

            // ✅ Update both slots’ statuses
            $mySlot->update(['status' => 'SWAP_PENDING']);
            $theirSlot->update(['status' => 'SWAP_PENDING']);

            return response()->json([
                'type' => 'success',
                'msg' => 'Swap request created successfully.',
                'data' => $swapRequest,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'type' => 'warning',
                'msg' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'msg' => 'Failed to create swap request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function respondToSwap(Request $request)
{
    try {
        // ✅ Validate input
        $validated = $request->validate([
            'requestId' => 'required|integer|exists:swaprequeststbl,id',
            'action' => 'required|string|in:ACCEPT,REJECT',
        ]);

        $userId = Auth::id();

        // ✅ Find swap request
        $swapRequest = SwapRequest::find($validated['requestId']);

        if (!$swapRequest) {
            return response()->json([
                'type' => 'warning',
                'msg' => 'Swap request not found.',
            ], 404);
        }

        // ✅ Get slots
        $mySlot = EventsTbl::find($swapRequest->mySlotId);
        $theirSlot = EventsTbl::find($swapRequest->theirSlotId);

        // ✅ Ensure slots exist first
        if (!$mySlot || !$theirSlot) {
            return response()->json([
                'type' => 'warning',
                'msg' => 'One or both slots no longer exist.',
            ], 400);
        }

        // ✅ Ensure logged-in user owns the target slot (theirSlot)
        if ($theirSlot->user_id !== $userId) {
            return response()->json([
                'type' => 'warning',
                'msg' => 'You are not authorized to respond to this swap request.',
            ], 403);
        }

        // ✅ Allow REJECT to go through even if slot status changed
        if ($validated['action'] === 'ACCEPT') {
            if (
                $mySlot->status !== 'SWAP_PENDING' ||
                $theirSlot->status !== 'SWAP_PENDING'
            ) {
                return response()->json([
                    'type' => 'warning',
                    'msg' => 'One or both slots are no longer available for swap.',
                ], 400);
            }

            // ACCEPT CASE
            $swapRequest->update(['status' => 'ACCEPTED']);

            // Swap the owners
            $tempUser = $mySlot->user_id;
            $mySlot->user_id = $theirSlot->user_id;
            $theirSlot->user_id = $tempUser;

            // Set both slots back to BUSY
            $mySlot->status = 'BUSY';
            $theirSlot->status = 'BUSY';

            $mySlot->save();
            $theirSlot->save();

            return response()->json([
                'type' => 'success',
                'msg' => 'Swap accepted successfully. Slots exchanged.',
                'data' => $swapRequest,
            ], 200);
        } else {
            // REJECT CASE
            $swapRequest->update(['status' => 'REJECTED']);

            // Revert both slots back to SWAPPABLE
            $mySlot->update(['status' => 'SWAPPABLE']);
            $theirSlot->update(['status' => 'SWAPPABLE']);

            return response()->json([
                'type' => 'success',
                'msg' => 'Swap request has Been Rejected Successfully',
                'data' => $swapRequest,
            ], 200);
        }

    } catch (ValidationException $e) {
        return response()->json([
            'type' => 'warning',
            'msg' => 'Validation failed.',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'type' => 'error',
            'msg' => 'Something went wrong while responding to the swap request.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
    public function incoming()
    {
        try {
            $userId = Auth::id();

            $incoming = SwapRequest::with(['mySlot', 'theirSlot', 'requester'])
                ->where('receiver_user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();

            if ($incoming->isEmpty()) {
                return response()->json([
                    'type' => 'warning',
                    'msg' => 'No incoming swap requests found.',
                    'data' => [],
                ], 200);
            }

            return response()->json([
                'type' => 'success',
                'msg' => 'Incoming swap requests fetched successfully.',
                'data' => $incoming,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'msg' => 'Failed to fetch incoming swap requests. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch outgoing swap requests
     * (requests sent by the logged-in user)
     */
    public function outgoing()
    {
        try {
            $userId = Auth::id();

            $outgoing = SwapRequest::with(['mySlot', 'theirSlot', 'receiver'])
                ->where('requester_user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();

            if ($outgoing->isEmpty()) {
                return response()->json([
                    'type' => 'warning',
                    'msg' => 'No outgoing swap requests found.',
                    'data' => [],
                ], 200);
            }

            return response()->json([
                'type' => 'success',
                'msg' => 'Outgoing swap requests fetched successfully.',
                'data' => $outgoing,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'msg' => 'Failed to fetch outgoing swap requests. ' . $e->getMessage(),
            ], 500);
        }
    }
}
