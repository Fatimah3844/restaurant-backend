<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    public function getNotifications(Request $request): JsonResponse
    {
        try {
            $user_id = $request->get('customer_id'); // from your checkUserId middleware
            Log::info('Customer ID: ' . $user_id);
            $notifications = Notification::where('customer_id', $user_id)->pluck('notification');
            return response()->json([
                'success' => true,
                'message' => 'Notifications retrieved successfully',
                'data' => $notifications
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
