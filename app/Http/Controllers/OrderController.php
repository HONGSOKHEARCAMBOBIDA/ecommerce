<?php

namespace App\Http\Controllers;
use Validator; // Add this to use the Validator
use App\Models\Order; // Ensure the User model is imported
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    //
    public function store(Request $request)
{
    // Validate the incoming request data
    $validator = Validator::make($request->all(), 
    [
        'status' => 'required|string|max:255',
        'total_price' => 'required|numeric|min:0',
    ]
);

    // Return validation errors if any
    if ($validator->fails())
    {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Get the authenticated user
    $user = Auth::user();

    // Check if user is authenticated
    if ($user !== null) {
        // Prepare the data for creating an order
        $data = $request->all();
        $data['user_id'] = $user->id;

        // Create the order
        $order = Order::create($data);

        // Return success response
        return response()->json(['message' => 'Order created successfully', 'order' => $order], 201);
    } else {
        // If the user is not authenticated, return an unauthorized error
        return response()->json(['message' => 'No permission to create'], 401);
    }
}
public function index()
{
    // Get the authenticated user
    $user = Auth::user();

    // Check if the user is authenticated
    if ($user !== null) 
    {
        // Retrieve all orders for the authenticated user
        $orders = Order::with(['orderItems', 'payment', 'shipping'])
                       ->where('user_id', $user->id) // Filter by user_id
                       ->get();

        // Return success response with the orders
        return response()->json(['message' => 'Orders retrieved successfully', 'orders' => $orders], 200);
    } 
    else 
    {
        // If the user is not authenticated, return an unauthorized error
        return response()->json(['message' => 'No permission to retrieve orders'], 401);
    }
}

public function show($id)
{
    // Find the order by its ID, including related data
    $order = Order::with(['orderItems', 'payment', 'shipping'])->find($id);

    // If the order is not found, return a 404 response
    if (!$order) {
        return response()->json(['message' => 'Order not found.'], 404);
    }

    // Return the order details in the response
    return response()->json(['order' => $order], 200);
}
public function update(Request $request, $id)
{
    // Get the currently authenticated user
    $user = Auth::user();
    
    // Check if the user is authenticated
    if ($user !== null) 
    {
        // Find the order by its ID
        $order = Order::find($id);
        
        // Check if the order exists and if the authenticated user owns the order
        if ($order !== null && $order->user_id === $user->id) {
            // Update the order with the new data
            $order->update($request->all());
            
            // Return a success response with the updated order
            return response()->json(['message' => 'Order updated successfully', 'order' => $order], 200);
        } else {
            // Return unauthorized error response if the order does not exist or the user is not the owner
            return response()->json(['message' => 'Unauthorized or order not found'], 401);
        }
    }

    // Return unauthorized error response if no user is authenticated
    return response()->json(['message' => 'Unauthorized'], 401);
}
public function destroy($id)
{
    // Get the currently authenticated user
    $user = Auth::user();
    
    // Check if the user is authenticated
    if ($user !== null) 
    {
        // Find the order by its ID
        $order = Order::find($id);
        
        // Check if the order exists and if the authenticated user is the owner
        if ($order !== null && $order->user_id === $user->id) 
        {
            // Delete the order
            $order->delete();
            
            // Return a success response
            return response()->json(['message' => 'Order deleted successfully']);
        } else {
            // Return an unauthorized error response if the user is not the owner or the order does not exist
            return response()->json(['message' => 'Unauthorized or order not found'], 401);
        }
    }

    // Return an unauthorized error response if no user is authenticated
    return response()->json(['message' => 'Unauthorized'], 401);
}





}
