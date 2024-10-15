<?php

namespace App\Http\Controllers;
use App\Models\Order;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator; // Add this to use the Validator

class OrderItemController extends Controller
{
    // Store a new order item
    public function store(Request $request)
    {
        // Validate the incoming request data if needed
        $data = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ]);
        
        // Create the order item
        $orderItem = OrderItem::create($data);
        
        // Return success response
        return response()->json(['message' => 'Order item created successfully', 'orderItem' => $orderItem], 201);
    }
    
    // Show a specific order item
    public function show($id)
    {
        // Find the order item by its ID
        $orderItem = OrderItem::find($id);
        
        // If the order item is not found, return a 404 response
        if (!$orderItem) {
            return response()->json(['message' => 'Order item not found.'], 404);
        }
        
        // Return the order item details in the response
        return response()->json(['orderItem' => $orderItem], 200);
    }
    
    // Get all order items for a specific order
    public function index($orderId)
    {
        // Check if the order exists
        if (!Order::find($orderId)) {
            return response()->json(['message' => 'Order not found.'], 404);
        }
    
        try {
            // Retrieve all order items for the specified order
            $orderItems = OrderItem::where('order_id', $orderId)->get();
    
            // Check if there are no items
            if ($orderItems->isEmpty()) {
                return response()->json(['message' => 'No items found for this order.'], 404);
            }
    
            // Return success response with the order items
            return response()->json(['message' => 'Order items retrieved successfully', 'orderItems' => $orderItems], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve order items', 'error' => $e->getMessage()], 500);
        }
    }
    
    
    // Update an existing order item
    public function update(Request $request, $id)
    {
        // Find the order item by its ID
        $orderItem = OrderItem::find($id);
        
        // Check if the order item exists
        if ($orderItem !== null) {
            // Update the order item with the new data
            $orderItem->update($request->all());
            
            // Return a success response with the updated order item
            return response()->json(['message' => 'Order item updated successfully', 'orderItem' => $orderItem], 200);
        } else {
            // Return an error response if the order item does not exist
            return response()->json(['message' => 'Order item not found'], 404);
        }
    }
    
    // Delete an order item
    public function destroy($id)
    {
        // Find the order item by its ID
        $orderItem = OrderItem::find($id);
        
        // Check if the order item exists
        if ($orderItem !== null) {
            // Delete the order item
            $orderItem->delete();
            
            // Return a success response
            return response()->json(['message' => 'Order item deleted successfully']);
        } else {
            // Return an error response if the order item does not exist
            return response()->json(['message' => 'Order item not found'], 404);
        }
    }
}
