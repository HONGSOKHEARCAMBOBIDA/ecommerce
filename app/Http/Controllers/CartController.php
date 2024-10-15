<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Validator; // Add this to use the Validator
use App\Models\Cart; // Ensure the User model is imported
class CartController extends Controller
{
    //
    public function store(Request $request)
    {
        // Step 1: Validate incoming request data
        // The 'product_id' must be provided and must exist in the 'products' table.
        // The 'quantity' must be provided, be an integer, and have a minimum value of 1.
        // The 'price' must be provided and be a numeric value.
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric',
        ]);
    
        // Step 2: Get the ID of the currently authenticated user
        // This assumes the user is logged in, and the authentication system is properly set up.
        $userId = auth()->id(); 
    
        // Step 3: Check if the product already exists in the cart for the current user
        // We search the 'carts' table for an existing cart item where:
        // - 'user_id' matches the logged-in user's ID
        // - 'product_id' matches the product ID from the request
        // If found, this means the product is already in the user's cart.
        $existingCartItem = Cart::where('user_id', $userId)
            ->where('product_id', $validated['product_id'])
            ->first();
    
        // Step 4: If the product is already in the cart, update the quantity
        if ($existingCartItem) {
            // Add the new quantity to the existing quantity in the cart
            $existingCartItem->quantity += $validated['quantity'];
            
            // Save the updated cart item back to the database
            $existingCartItem->save();
    
            // Return a JSON response indicating that the cart was updated successfully
            return response()->json([
                'message' => 'Cart updated successfully',
                'cart' => $existingCartItem, // Include the updated cart item in the response
            ]);
        } else {
            // Step 5: If the product is not in the cart, create a new cart item
            $newCartItem = Cart::create([
                'user_id' => $userId,             // The ID of the logged-in user
                'product_id' => $validated['product_id'], // The product ID from the request
                'quantity' => $validated['quantity'],     // The quantity from the request
                'price' => $validated['price'],           // The price from the request
            ]);
    
            // Return a JSON response indicating that a new product was added to the cart
            return response()->json([
                'message' => 'Product added to cart',
                'cart' => $newCartItem, // Include the new cart item in the response
            ]);
        }
    }
    
    
public function getCart()
{
    // Get the authenticated user
    $user = Auth::user();

    // Check if the user is authenticated
    if ($user !== null) {
        // Retrieve the cart items for the authenticated user
        $cartItems = Cart::with('product')->where('user_id', $user->id)->get();

        // Return the cart items in a JSON response
        return response()->json(['cart_items' => $cartItems], 200);
    } else {
        // If the user is not authenticated, return an unauthorized response
        return response()->json(['message' => 'Unauthorized'], 401);
    }
}
public function update(Request $request, $cartId)
{
    $user = Auth::user();
    
    if ($user !== null) {
        $cart = Cart::find($cartId);
        
        if ($cart !== null && $cart->user_id === $user->id) {
            // Validate the incoming request data
            $validated = $request->validate([
                'quantity' => 'required|integer|min:1',
                'price' => 'sometimes|numeric', // Optionally allow updating the price
            ]);

            // Update the cart with the validated data
            $cart->update($validated);
            
            return response()->json(['message' => 'Cart updated successfully']);
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }

    return response()->json(['message' => 'Unauthorized'], 401);
}

public function destroy($id)
{
    // Get the currently authenticated user
    $user = Auth::user();
    
    // Check if the user is authenticated
    if ($user === null) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    // Find the product by its ID
    $cart = Cart::find($id);
    
    // Check if the product exists
    if ($cart === null) {
        return response()->json(['message' => 'card not found.'], 404);
    }

    // You can add additional checks here to ensure the user has permission to delete the product
    // For example, checking if the user is the owner of the product, if applicable

    // Delete the product
    $cart->delete();
    
    // Return a success response
    return response()->json(['message' => 'cart deleted successfully.'], 200);
}
public function increaseQuantity($cartId)
{
    $user = Auth::user();
    
    if ($user !== null) {
        $cart = Cart::where('id', $cartId)->where('user_id', $user->id)->first();

        if ($cart !== null) {
            $cart->quantity++;
            $cart->save();
            return response()->json(['message' => 'Quantity increased', 'cart' => $cart]);
        }
        return response()->json(['message' => 'Cart item not found'], 404);
    }
    return response()->json(['message' => 'Unauthorized'], 401);
}

public function decreaseQuantity($cartId)
{
    $user = Auth::user();
    
    if ($user !== null) {
        $cart = Cart::where('id', $cartId)->where('user_id', $user->id)->first();

        if ($cart !== null && $cart->quantity > 1) {
            $cart->quantity--;
            $cart->save();
            return response()->json(['message' => 'Quantity decreased', 'cart' => $cart]);
        }
        return response()->json(['message' => 'Cart item not found or cannot decrease further'], 404);
    }
    return response()->json(['message' => 'Unauthorized'], 401);
}



}
