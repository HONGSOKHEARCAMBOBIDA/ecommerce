<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Validator; // Add this to use the Validator
use App\Models\Product; // Ensure the User model is imported
class ProductController extends Controller
{
    //
    public function store(Request $request)
{
    $user = Auth::user();
    
    // Check if the user is authenticated
    if ($user == null) {
        return response()->json(['message' => 'No permission to create'], 401);
    }

    // Validate the incoming request data
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'description' => 'required|string',
        'price' => 'required|numeric',
        'stock' => 'required|integer',
        'category_id' => 'required|exists:categories,id', // Ensures category exists
        'image' => 'nullable|image|mimes:jpg,png,jpeg,gif|max:2048', // File validation
    ]);

    // If validation fails, return error
    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $data = $request->all();

    // Handle image upload
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $name = time() . '.' . $image->getClientOriginalExtension();
        $destinationPath = public_path('/products'); // Ensure 'products' directory exists
        $image->move($destinationPath, $name);
        $data['image'] = $name; // Save the image name to the data array
    }

    // Save the product
    $data['user_id'] = $user->id; // Track the user who created the product
    $product = Product::create($data);

    // Return success response
    return response()->json(['message' => 'Product created successfully', 'product' => $product], 201);
}
public function show($id)
{
    // Find the product by its ID
    $product = Product::find($id);

    // If the product is not found, return a 404 response
    if (!$product) {
        return response()->json(['message' => 'Product not found.'], 404);
    }

    // Return the product details in the response
    return response()->json(['product' => $product], 200);
}
public function index()
{
    // Get all products along with their category and order items, and paginate them
    $products = Product::with(['category', 'orderItems'])->paginate(100); // You can adjust the pagination as needed

    // Return the list of products
    return response()->json(['products' => $products], 200);
}

public function update(Request $request, $productId)
{
   
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'required|numeric',
        'stock' => 'required|integer',
        'category_id' => 'nullable|exists:categories,id',
        'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Ensure image is valid
    ]);

    // Get the currently authenticated user
    $user = Auth::user();
    
    // Check if the user is authenticated
    if ($user === null) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }
    
    // Find the product by its ID
    $product = Product::find($productId);
    
    // Check if the product exists
    if ($product === null) {
        return response()->json(['message' => 'Product not found.'], 404);
    }

    // Handle image upload if an image is provided
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $name = time() . '.' . $image->getClientOriginalExtension();
        $destinationPath = public_path('/products');

        // Ensure the products directory exists and is writable
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true); // Create directory if it doesn't exist
        }

        // Move the uploaded image to the destination path
        $image->move($destinationPath, $name);
        
        // Update the data array with the new image name
        $validatedData['image'] = $name;
        
        // Remove the old image if it exists
        if ($product->image) {
            $oldImagePath = public_path('products/' . $product->image);
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath); // Delete the old image file
            }
        }
    }

    // Update the product with the new data
    $product->update($validatedData);
    
    // Return a success response
    return response()->json(['message' => 'Product updated successfully']);
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
    $product = Product::find($id);
    
    // Check if the product exists
    if ($product === null) {
        return response()->json(['message' => 'Product not found.'], 404);
    }

    // You can add additional checks here to ensure the user has permission to delete the product
    // For example, checking if the user is the owner of the product, if applicable

    // Delete the product
    $product->delete();
    
    // Return a success response
    return response()->json(['message' => 'Product deleted successfully.'], 200);
}
public function getProductsByCategory($categoryId)
{
    // Validate that the category ID exists in the categories table
    $products = Product::where('category_id', $categoryId)->with('category')->get();

    // Check if any products were found for the given category ID
    if ($products->isEmpty()) {
        return response()->json(['message' => 'No products found for this category.'], 404);
    }

    // Return the list of products in JSON format
    return response()->json(['products' => $products], 200);
}


}
