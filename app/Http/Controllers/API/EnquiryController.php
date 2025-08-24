<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use Illuminate\Http\Request;

class EnquiryController extends Controller
{
    public function showForCashier()
    {
        $enquiries = Enquiry::with('customer')->get();
        return response()->json(['success' => true, 'data' => $enquiries]);
    }

    public function receive($id)
    {
        $enquiry = Enquiry::find($id);
        if (!$enquiry) {
            return response()->json(['message' => 'Enquiry not found'], 404);
        }

        
        $enquiry->received = true;
        $enquiry->save();

        return response()->json([
            'success' => true,
            'message' => 'Enquiry marked as received',
            'data' => $enquiry
        ]);
    }

    public function listForAdmin()
    {
        $enquiries = Enquiry::with('customer')->get();
        return response()->json(['success' => true, 'data' => $enquiries]);
    }

    public function update(Request $request, $id)
    {
        $enquiry = Enquiry::find($id);
        if (!$enquiry) {
            return response()->json(['message' => 'Enquiry not found'], 404);
        }

        $request->validate(['content' => 'required|string']);

        $enquiry->content = $request->content;
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
