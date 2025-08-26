<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Middleware\checkUserId;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\EnquiryController;

//Authentication routes for all users

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('checkUserId')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::get('/test', function() {
    return "Middleware works";
})->middleware('checkUserId');
});
//Users CURD routes (admin)
Route::middleware(['checkUserId', 'admin'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});


//Categories CRUD routes (admin)
Route::middleware(['checkUserId', 'admin'])->group(function () {
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
});
//products CRUD routes (admin)
Route::middleware(['checkUserId', 'admin'])->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
});


///////////////////////////////////////////////////////////////

// Public routes for menu display (no authentication required)
Route::get('/menu', [OrderController::class, 'showMenu']);
Route::get('/menu/products', [OrderController::class, 'getProducts']);
Route::get('/menu/categories', [OrderController::class, 'getCategories']);

// Customer ordering routes (requires authentication)
Route::middleware(['checkUserId'])->group(function () {
    Route::post('/orders', [OrderController::class, 'createOrder']); //done
    Route::put('/orders/{id}/items', [OrderController::class, 'updateOrderItems']);//done
    Route::post('/orders/{id}/submit', [OrderController::class, 'submitOrder']);//done
    Route::get('/orders/{id}/track', [OrderController::class, 'trackOrder']);//done
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancelOrder']);//done
    Route::get('/my-orders', [OrderController::class, 'getCustomerOrders']);//done
});

// Admin routes for order management
Route::middleware(['checkUserId', 'admin'])->group(function () {
    Route::get('/orders/history', [OrderController::class, 'getOrderHistory']);
    Route::put('/orders/{id}/status', [OrderController::class, 'updateOrderStatus']);
});

// Cashier routes for order management
Route::middleware(['checkUserId', 'cashier'])->group(function () {
    Route::get('/cashier_orders', [OrderController::class, 'getCashierOrders']);//current 
    Route::put('/orders/{id}/status', [OrderController::class, 'updateOrderStatus']);
});





// (Cashier) enquiries routes
Route::middleware(['checkUserId', 'cashier'])->group(function () {
    Route::get('/cashier/enquiries/show', [EnquiryController::class, 'showForCashier']);
    Route::put('/cashier/enquiries/receive/{id}', [EnquiryController::class, 'receive']);
});

// (Admin) enquiries routes
Route::middleware(['checkUserId', 'admin'])->group(function () {
    Route::get('/admin/enquiries/list', [EnquiryController::class, 'listForAdmin']);
    Route::put('/admin/enquiries/update/{id}', [EnquiryController::class, 'update']);
    Route::delete('/admin/enquiries/delete/{id}', [EnquiryController::class, 'delete']);
});
