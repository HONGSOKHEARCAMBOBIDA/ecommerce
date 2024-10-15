<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator; // For validating the request data
use App\Models\Shipping;
use App\Models\Cart;
use App\Models\OrderItem;

class ShippingController extends Controller
{
    // Store shipping details and transfer cart items to order items
    public function store(Request $request)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'shipping_address' => 'required|string|max:255',
            'shipping_method' => 'required|string|max:255',
        ]);

        // Check if the validation fails
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // Create the shipping entry
            $shipping = Shipping::create($validator->validated());

            // Get the authenticated user's ID
            $userId = auth()->id();
            $cartItems = Cart::where('user_id', $userId)->get();

            // Check if there are items in the cart
            if ($cartItems->isEmpty()) {
                DB::rollBack();
                return response()->json(['message' => 'No items in cart to transfer.'], 422);
            }

            // Transfer each cart item to the order_item table
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $request->order_id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ]);
            }

            // Clear the cart after transferring items to order_item
            Cart::where('user_id', $userId)->delete();

            // Commit the transaction
            DB::commit();

            // Return success response
            return response()->json([
                'message' => 'Shipping created successfully and cart items transferred to order items.',
                'shipping' => $shipping
            ], 201);

        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create shipping or transfer cart items.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Show a specific shipping entry by ID
    public function show($id)
    {
        $shipping = Shipping::find($id);
        
        if (!$shipping) {
            return response()->json(['message' => 'Shipping not found.'], 404);
        }
        
        return response()->json(['shipping' => $shipping], 200);
    }

    // List all shipping entries with pagination
    public function index()
    {
        $shipping = Shipping::paginate(10);
        return response()->json($shipping, 200);
    }

    // Update a specific shipping entry
    public function update(Request $request, $id)
    {
        $shipping = Shipping::find($id);
        
        if ($shipping !== null) {
            $shipping->update($request->all());
            return response()->json(['message' => 'Shipping updated successfully', 'shipping' => $shipping], 200);
        } else {
            return response()->json(['message' => 'Shipping not found'], 404);
        }
    }

    // Delete a specific shipping entry
    public function destroy($id)
    {
        $shipping = Shipping::find($id);
        
        if ($shipping !== null) {
            $shipping->delete();
            return response()->json(['message' => 'Shipping deleted successfully']);
        } else {
            return response()->json(['message' => 'Shipping not found'], 404);
        }
    }
}
