<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Display menu with products and categories for customers
     */
    public function showMenu(): JsonResponse
    {
        try {
            $categories = Category::with(['products' => function ($query) {
                $query->select('id', 'name', 'description', 'image_url', 'price', 'category_id');
            }])->get();

            return response()->json([
                'success' => true,
                'message' => 'Menu retrieved successfully',
                'data' => $categories
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve menu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all products with categories
     */
    public function getProducts(): JsonResponse
    {
        try {
            $products = Product::with('category')->get();

            return response()->json([
                'success' => true,
                'message' => 'Products retrieved successfully',
                'data' => $products
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all categories
     */
    public function getCategories(): JsonResponse
    {
        try {
            $categories = Category::get();

            return response()->json([
                'success' => true,
                'message' => 'Categories retrieved successfully',
                'data' => $categories
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new order
     */
    public function createOrder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:users,id',
            'table_id' => 'nullable|exists:tables,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $totalPrice = 0;
            $orderItems = [];

            // Calculate total price and prepare order items
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $itemPrice = $product->price * $item['quantity'];
                $totalPrice += $itemPrice;

                $orderItems[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $itemPrice,
                ];
            }

            // Create the order
            $order = Order::create([
                'customer_id' => $request->customer_id,
                'table_id' => $request->table_id,
                'total_price' => $totalPrice,
                'status' => 'pending',
            ]);

            // Create order items
            foreach ($orderItems as $item) {
                $item['order_id'] = $order->id;
                OrderItem::create($item);
            }

            DB::commit();

            // Load the order with relationships
            $order->load(['customer', 'table', 'items.product']);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update order items (add/update/delete items)
     */
    public function updateOrderItems(Request $request, $orderId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:0', // 0 quantity means delete item
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $order = Order::find($orderId);
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        // Only allow updates for pending orders
        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update order items. Order is no longer pending.'
            ], 400);
        }

        if ($order->customer_id !== $request->get('customer_id')) {
            return response()->json(['success' => false, 'message' => 'Not authorized'], 403);
        }

        DB::beginTransaction();

        try {
            $totalPrice = 0;

            // Clear existing order items
            OrderItem::where('order_id', $orderId)->delete();

            // Add new/updated items
            foreach ($request->items as $item) {
                if ($item['quantity'] > 0) {
                    $product = Product::find($item['product_id']);
                    $itemPrice = $product->price * $item['quantity'];
                    $totalPrice += $itemPrice;

                    OrderItem::create([
                        'order_id' => $orderId,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $itemPrice,
                    ]);
                }
            }

            // Update order total price
            $order->update(['total_price' => $totalPrice]);
            $order->statusChanges()->create([
                'order_id' => $order->id,
                'status' => 'pending',
                'created_at' => now(),
            ]);

            DB::commit();

            // Load the updated order with relationships
            $order->load(['customer', 'table', 'items.product']);

            return response()->json([
                'success' => true,
                'message' => 'Order items updated successfully',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order items',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    // **
    //      * Submit order (change status from pending to confirmed)
    //      */
    public function submitOrder(Request $request, $orderId): JsonResponse
    {
        $order = Order::find($orderId);
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be submitted. Current status: ' . $order->status
            ], 400);
        }
        if ($order->customer_id !== $request->get('customer_id')) {
            return response()->json(['success' => false, 'message' => 'Not authorized'], 403);
        }

        try {
            $order->update(['status' => 'confirmed']);

            // Create status change record
            $order->statusChanges()->create([
                'order_id' => $order->id,
                'status' => 'confirmed',
                'created_at' => now(),
            ]);

            $order->load(['customer', 'table', 'items.product']);

            return response()->json([
                'success' => true,
                'message' => 'Order submitted successfully',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit order',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    // Track order status
    public function trackOrder(Request $request, $orderId): JsonResponse
    {
        try {
            $order = Order::with(['customer', 'table', 'items.product', 'statusChanges'])
                ->find($orderId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }
            if ($order->customer_id !== $request->get('customer_id')) {
                return response()->json(['success' => false, 'message' => 'Not authorized'], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order tracking information retrieved successfully',
                'data' => [
                    'order' => $order,
                    'status_history' => $order->statusChanges()->orderBy('created_at', 'desc')->get()
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve order tracking information',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // cancel order
    public function cancelOrder(Request $request, $orderId): JsonResponse

    {


        try {
            $order = Order::find($orderId);
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }
            if ($order->customer_id !== $request->get('customer_id')) {
                return response()->json(['success' => false, 'message' => 'Not authorized'], 403);
            }
            // Only allow cancellation for pending
            if (!in_array($order->status, ['pending'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order cannot be cancelled. Current status: ' . $order->status
                ], 400);
            }

            $order->update(['status' => 'cancelled']);

            // Create status change record
            $order->statusChanges()->create([
                'order_id' => $order->id,
                'status' => 'cancelled',
                'created_at' => now(),
            ]);

            $order->load(['customer', 'table', 'items.product']);

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    //see customer's orders
    public function getCustomerOrders(Request $request): JsonResponse
    {
        try {
            $customerId = $request->get('customer_id');
            $orders = Order::with(['items.product'])
                ->where('customer_id', $customerId)
                ->where('status', '!=', 'cancelled')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Customer orders retrieved successfully',
                'data' => $orders
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve customer orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getCashierOrders(Request $request): JsonResponse
    {
        try {
            $orders = Order::with(['items.product'])
                ->where('status', '!=', 'cancelled')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Customer orders retrieved successfully',
                'data' => $orders
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve customer orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //update order status (admin and cashier)
    public function updateOrderStatus(Request $request, $orderId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,confirmed,In Preperation,Ready,Delivered,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $order = Order::find($orderId);
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        // Prevent changing status to pending
        if ($request->status === 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot change order status back to pending.'
            ], 400);
        }

        // Prevent changing status to cancelled (customers should do that)
        if ($request->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot change order status to cancelled. Customers must cancel their own orders.'
            ], 400);
        }

        // Define valid status transitions
        $validTransitions = [
            'pending' => ['confirmed', 'cancelled', 'In Preperation'],
            'confirmed' => ['In Preperation', 'cancelled'],
            'In Preperation' => ['Ready', 'cancelled'],
            'Ready' => ['Delivered', 'cancelled'],
            'Delivered' => [],
            'cancelled' => [],
        ];

        if (!in_array($request->status, $validTransitions[$order->status])) {
            return response()->json([
                'success' => false,
                'message' => "Invalid status transition from {$order->status} to {$request->status}."
            ], 400);
        }

        try {
            $order->update(['status' => $request->status]);

            // Create status change record
            $order->statusChanges()->create([
                'order_id' => $order->id,
                'status' => $request->status,
                'updated_at' => now(),
            ]);
            Notification::create([
                'customer_id' => $order->customer_id, // foreign key in notifications table
                'notification' => "Your order #{$order->id} status has been updated to {$request->status}.",
            ]);

            $order->load(['customer', 'table', 'items.product']);

            return response()->json([
                'success' => true,
                'message' => 'order status updated successfully',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve customer orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getNotifications(Request $request): JsonResponse
    {
        try {
            $user_id = $request->get('cutomer_id'); // from your checkUserId middleware

            $notifications = Notification::where('customer_id', $user_id)
                ->get();

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
