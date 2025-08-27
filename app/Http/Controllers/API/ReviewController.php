<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Review;

class ReviewController extends Controller
{
    //submit review by customer
    public function rateOrder(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);
        $id = $request->get('order_id');
        // Find the order by ID and ensure it belongs to the authenticated user
        $order = Order::where('id', $id)
            ->where('customer_id', $request->get('customer_id'))
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or does not belong to the user'
            ], 404);
        }



        // Create the review
        $review = new Review();
        $review->order_id = $order->id;
        $review->customer_id = $request->get('customer_id');
        $review->rating = $request->rating;
        $review->comment = $request->comment;
        $review->save();

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully',
            'data' => [
                'review_id' => $review->id,
                'order_id' => $order->id,
                'customer_id' => $review->customer_id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'created_at' => $review->created_at,
            ]
        ], 201);
    }
    //read all reviews
    public function readReviews()
    {
        $reviews = Review::with('customer:id,name')->get();
        return response()->json([
            'success' => true,
            'data' => $reviews
        ], 200);
    }
    //delete review by id
    public function deleteReview(Request $request)
    {
        $id = $request->get('review_id');
        $customer_id = $request->get('customer_id');
        if ($customer_id !== null) {
            $review = Review::where('id', $id)
                ->where('customer_id', $customer_id)
                ->first();
        } else {
            $review = Review::where('id', $id)->first();
        }
        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found or does not belong to the user'
            ], 404);
        }

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully'
        ], 200);
    }
    //update review by id
    public function updateReview(Request $request)
    {
        $id = $request->get('review_id');
        $customer_id = $request->get('customer_id');
        $review = Review::where('id', $id)
            ->where('customer_id', $customer_id)
            ->first();

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found or does not belong to the user'
            ], 404);
        }

        // Validate the incoming request
        $request->validate([
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'comment' => 'sometimes|nullable|string|max:1000',
        ]);

        // Update the review fields if provided
        if ($request->has('rating')) {
            $review->rating = $request->rating;
        }
        if ($request->has('comment')) {
            $review->comment = $request->comment;
        }
        $review->save();

        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully',
            'data' => [
                'review_id' => $review->id,
                'order_id' => $review->order_id,
                'customer_id' => $review->customer_id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'updated_at' => $review->updated_at,
            ]
        ], 200);
    }
}
