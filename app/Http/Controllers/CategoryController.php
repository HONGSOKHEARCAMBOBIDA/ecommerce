<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator; // Add this to use the Validator
use App\Models\Category; // Ensure the User model is imported


class CategoryController extends Controller
{
    //
    public function store(Request $request)
{
    // Validator for input
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'description' => 'required|string|max:255',
    ]);

    // Return errors if validation fails
    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }

    // Create the category
    $category = Category::create($request->all());

    // Return success response
    return response()->json(['message' => 'Category created successfully'], 200);
}
// public function update(Request $request, $id)
// {
//     // Find the category by its ID
//     $category = Category::find($id);

//     if (!$category) {
//         return response()->json(['message' => 'Category not found.'], 404);
//     }

//     // Validate request data
//     $validator = Validator::make($request->all(), [
//         'name' => 'required|string|max:255',
//         'description' => 'required|string|max:255',
//     ]);

//     if ($validator->fails()) {
//         return response()->json(['error' => $validator->errors()], 422);
//     }

//     // Update the category
//     $category->update($request->all());

//     return response()->json(['message' => 'Category updated successfully.'], 200);
// }
public function update(Request $request, $id)
{
    // Find the payment by its ID
    $category = Category::find($id);
    
    // Check if the payment exists
    if ($category  !== null) {
        // Update the payment with the request data (without validation)
        $category ->update($request->all());
        
        // Return a success response with the updated payment
        return response()->json(['message' => 'Category updated successfully', 'category' =>  $category], 200);
    } else {
        // Return an error response if the payment does not exist
        return response()->json(['message' => 'category not found'], 404);
    }
}
public function destroy($id)
{
    // Find the category by its ID
    $category = Category::find($id);

    if (!$category) {
        return response()->json(['message' => 'Category not found.'], 404);
    }

    // Delete the category
    $category->delete();

    return response()->json(['message' => 'Category deleted successfully.'], 200);
}
public function show($id)
{
    // Find the category by its ID
    $category = Category::find($id);

    // If the category is not found, return a 404 response
    if (!$category) {
        return response()->json(['message' => 'Category not found.'], 404);
    }

    // Return the category details in the response
    return response()->json(['category' => $category], 200);
}
public function index()
{
    // Get all categories with their associated products
    $categories = Category::with('products')->paginate(10); // Adjust pagination as needed

    // Return the list of categories
    return response()->json(['categories' => $categories], 200);
}

}
