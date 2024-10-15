<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController; // Include CartController for cart-related routes
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ShippingController;

// Authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/update', [AuthController::class, 'update'])->middleware('auth:api');
Route::post('/delete', [AuthController::class, 'destroy'])->middleware('auth:api');
Route::post('/me', [AuthController::class, 'me'])->middleware('auth:api');
Route::get('/users', [AuthController::class, 'getAllUsers'])->middleware('auth:api');

// Category routes
Route::post('/category', [CategoryController::class, 'store'])->middleware('auth:api');
Route::get('/category/{id}', [CategoryController::class, 'show'])->middleware('auth:api');
Route::put('/category/{id}', [CategoryController::class, 'update'])->middleware('auth:api');
Route::delete('/category/{id}', [CategoryController::class, 'destroy'])->middleware('auth:api');
Route::get('/category', [CategoryController::class, 'index'])->middleware('auth:api');

// Product routes
Route::post('/product', [ProductController::class, 'store'])->middleware('auth:api');
Route::get('/product/{id}', [ProductController::class, 'show'])->middleware('auth:api');
Route::post('/product/{id}', [ProductController::class, 'update'])->middleware('auth:api');
Route::delete('/product/{id}', [ProductController::class, 'destroy'])->middleware('auth:api');
Route::get('/products', [ProductController::class, 'index'])->middleware('auth:api');
Route::get('/products/category/{categoryId}', [ProductController::class, 'getProductsByCategory'])->middleware('auth:api');


// Cart routes
Route::post('/cart', [CartController::class, 'store'])->middleware('auth:api');
Route::get('/cart', [CartController::class, 'getCart'])->middleware('auth:api');
Route::put('/cart/{cartId}', [CartController::class, 'update'])->middleware('auth:api');
Route::delete('/cart/{cartId}', [CartController::class, 'destroy'])->middleware('auth:api');
Route::post('/cart/increase/{id}', [CartController::class, 'increaseQuantity'])->middleware('auth:api');
Route::post('/cart/decrease/{id}', [CartController::class, 'decreaseQuantity'])->middleware('auth:api');

// order route
Route::get('/order', [OrderController::class, 'index'])->middleware('auth:api'); // Get all orders for the authenticated user
Route::post('/order', [OrderController::class, 'store'])->middleware('auth:api'); // Create a new order
Route::get('/order/{id}', [OrderController::class, 'show'])->middleware('auth:api'); // Get a specific order by ID
Route::put('/order/{id}', [OrderController::class, 'update'])->middleware('auth:api'); // Update an existing order
Route::delete('/order/{id}', [OrderController::class, 'destroy'])->middleware('auth:api'); // Delete an order
// shipping
Route::get('/shipping', [ShippingController::class, 'index'])->middleware('auth:api'); // Get all shipping entries for the authenticated user
Route::post('/shipping', [ShippingController::class, 'store'])->middleware('auth:api'); // Create a new shipping entry
Route::get('/shipping/{id}', [ShippingController::class, 'show'])->middleware('auth:api'); // Get a specific shipping entry by ID
Route::put('/shipping/{id}', [ShippingController::class, 'update'])->middleware('auth:api'); // Update an existing shipping entry
Route::delete('/shipping/{id}', [ShippingController::class, 'destroy'])->middleware('auth:api'); // Delete a shipping entry
// payment route
Route::get('/payments', [PaymentController::class, 'index'])->middleware('auth:api'); // Get all payments for the authenticated user
Route::post('/payments', [PaymentController::class, 'store'])->middleware('auth:api'); // Create a new payment
Route::get('/payments/{id}', [PaymentController::class, 'show'])->middleware('auth:api'); // Get a specific payment by ID
Route::put('/payments/{id}', [PaymentController::class, 'update'])->middleware('auth:api'); // Update an existing payment
Route::delete('/payments/{id}', [PaymentController::class, 'destroy'])->middleware('auth:api'); // Delete a payment

Route::get('/order_items/{orderId}', [OrderItemController::class, 'index'])->middleware('auth:api');
// Get all payments for the authenticated user
Route::post('/order_items', [OrderItemController::class, 'store'])->middleware('auth:api'); // Create a new payment
//Route::get('/order_items/{id}', [OrderItemController::class, 'show'])->middleware('auth:api'); // Get a specific payment by ID
Route::put('/order_items/{id}', [OrderItemController::class, 'update'])->middleware('auth:api'); // Update an existing payment
Route::delete('/order_items/{id}', [OrderItemController::class, 'destroy'])->middleware('auth:api'); // Delete a payment
