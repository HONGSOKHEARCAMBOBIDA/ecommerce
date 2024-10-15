<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use Validator; // Add this to use the Validator
use App\Models\User; // Ensure the User model is imported

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validator for input
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email', // Ensure email is unique
                'password' => 'required|min:6', // Add a minimum length to the password
                'address' => 'nullable|string|max:255', // Optional, can be null
                'phone_number' => 'nullable|string|max:15', // Optional, allow null
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate the image if provided
            ]
        );
    
        // Return errors if validation fails
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422); // Reference errors(), not error()
        }
    
        $input = $request->all();
    
        // Upload image if provided
        if ($request->hasFile('profile_image')) {
            $image = $request->file('profile_image');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/images'); // Ensure 'images' directory exists
            $image->move($destinationPath, $name);
            $input['profile_image'] = $name; // Store image name in the input array
        }
    
        // Hash the password before saving the user
        $input['password'] = bcrypt($input['password']);
    
        // Create the user
        $user = User::create($input);
    
        // Return success response
        return response()->json(['message' => 'User registered successfully'], 200);
    }
    
    
    public function login(Request $request)
    {
        $email = $request->email;
        $password = $request->password;

        if (Auth::attempt(['email' => $email, 'password' => $password])) {
            $user = Auth::user();

            // Check if Passport or Sanctum is used
            if (method_exists($user, 'createToken')) {
                $token = $user->createToken('salaitapp')->accessToken;
            } else {
                // Handle the case for Sanctum
                $token = $user->createToken('salaitapp')->plainTextToken;
            }

            return response()->json([
                'message' => 'User logged in successfully',
                'user' => $user,
                'token' => $token
            ], 200);
        }

        return response()->json(['message' => 'Unauthorized'], 401);
    }
    public function update(Request $request)
{
    // Get the authenticated user
    $user = Auth::user();

    // Check if the user is authenticated
    if ($user === null) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    // Validate the request including image file
    // $request->validate([
        
    //     'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Image validation
    // ]);

    // Retrieve all input data from the request
    $data = $request->all();

    // Check if the request has an uploaded file named 'image'
    if ($request->hasFile('profile_image')) {
        // Process the uploaded image
        $image = $request->file('profile_image');
        $name = time() . '.' . $image->getClientOriginalExtension();
        $destinationPath = public_path('/images');
        
        // Ensure the 'images' directory exists
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        // Move the uploaded image to the 'images' directory
        $image->move($destinationPath, $name);

        // Set the profile_image field with the new image name
        $data['profile_image'] = $name;

        // Delete the old image if it exists and is not the default image
        $oldImage = $user->profile_image;
        if ($oldImage && $oldImage != 'default.jpg') {
            $oldImagePath = public_path('images/' . $oldImage);
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath); // Delete the old image
            }
        }
    }

    // Update the user's profile image and other data
    $user->update($data);

    // Return a response indicating success
    return response()->json(['message' => 'Profile updated successfully.']);
}

    
    public function destroy()
    {
        $user = Auth::user();
    
        if ($user!=null) {
            // Optional: Handle related records or perform additional cleanup if needed
    
            $user->delete();
    
            return response()->json(['message' => 'User deleted successfully.'], 200);
        } else {
            return response()->json(['message' => 'User not found.'], 404);
        }
    }
    public function me(){
        return response()->json(['user'=>Auth::user()],200);
    }
    public function getAllUsers()
{
    // Retrieve all users from the User model
    $users = User::all();

    // Return the users as JSON response
    return response()->json(['users' => $users], 200);
}

    
    
}
    