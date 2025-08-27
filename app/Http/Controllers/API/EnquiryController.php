<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class EnquiryController extends Controller


{

    //submit enquiry by customer
    public function submitEnquiry(Request $request): JsonResponse
    {
        try {
            // Validate the incoming request
            $validator = Validator::make($request->all(), [
                'content' => 'required|string|max:1000',
                'customer_id' => 'required|integer|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create the enquiry
            $enquiry = Enquiry::create([
                'content' => $request->content,
                'customer_id' => $request->customer_id,
                'received' => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Enquiry submitted successfully',
                'data' => [
                    'enquiry_id' => $enquiry->id,
                    'content' => $enquiry->content,
                    'customer_id' => $enquiry->customer_id,
                    'received' => $enquiry->received,
                    'created_at' => $enquiry->created_at,
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit enquiry',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    // public function showForCashier()
    // {
    //     $enquiries = Enquiry::with('customer')->get();
    //     return response()->json(['success' => true, 'data' => $enquiries]);
    // }

    // public function receive($id)
    // {
    //     $enquiry = Enquiry::find($id);
    //     if (!$enquiry) {
    //         return response()->json(['message' => 'Enquiry not found'], 404);
    //     }


    //     $enquiry->received = true;
    //     $enquiry->save();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Enquiry marked as received',
    //         'data' => $enquiry
    //     ]);
    // }

    public function listForAdmin()
    {
        $enquiries = Enquiry::with('customer')->get();
        return response()->json(['success' => true, 'data' => $enquiries]);
    }

    public function receive(Request $request, $id)
    {
        $enquiry = Enquiry::find($id);
        if (!$enquiry) {
            return response()->json(['message' => 'Enquiry not found'], 404);
        }


        $enquiry->received = true;
        $enquiry->save();

        return response()->json(['success' => true, 'data' => $enquiry]);
    }
    public function respond(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'response' => 'required|string|max:400',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $enquiry = Enquiry::find($id);
        if (!$enquiry) {
            return response()->json(['message' => 'Enquiry not found'], 404);
        }

        $enquiry->response = $request->response;
        $enquiry->received = true; // Mark as received when responded
        $enquiry->save();

        return response()->json(['success' => true, 'data' => $enquiry]);
    }

    public function delete($id)
    {
        $enquiry = Enquiry::find($id);
        if (!$enquiry) {
            return response()->json(['message' => 'Enquiry not found'], 404);
        }

        $enquiry->delete();

        return response()->json([
            'success' => true,
            'message' => 'Enquiry deleted'
        ]);
    }
}
