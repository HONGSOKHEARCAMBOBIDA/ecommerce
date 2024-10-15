<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator; // Add this to use the Validator
use App\Models\Payment;
class PaymentController extends Controller
{
    //
    public function store(Request $request)
{
    // Validate the incoming request data
    $data = $request->validate([
        'order_id' => 'required|exists:orders,id',
        'payment_method' => 'required|string|max:255',
        'payment_status' => 'required|string|max:255',
        'amount_paid' => 'required|numeric|min:0', // Corrected field name
    ]);
    
    // Create the payment
    $payment = Payment::create($data);
    
    // Return success response
    return response()->json(['message' => 'Payment created successfully', 'payment' => $payment], 201);
}
public function index()
{
    // Retrieve paginated payments (10 per page)
    $payments = Payment::paginate(10);
    
    // Return the paginated payments as a JSON response
    return response()->json($payments, 200);
}
public function show($id)
{
    // Find the payment by its ID
    $payment = Payment::find($id);
    
    // If the payment is not found, return a 404 response
    if (!$payment) 
    {
        return response()->json(['message' => 'Payment not found.'], 404);
    }
    
    // Return the payment details in the response
    return response()->json(['payment' =>  $payment], 200);
}
public function update(Request $request, $id)
{
    // Find the payment by its ID
    $payment = Payment::find($id);
    
    // Check if the payment exists
    if ($payment !== null) {
        // Update the payment with the request data (without validation)
        $payment->update($request->all());
        
        // Return a success response with the updated payment
        return response()->json(['message' => 'Payment updated successfully', 'payment' => $payment], 200);
    } else {
        // Return an error response if the payment does not exist
        return response()->json(['message' => 'Payment not found'], 404);
    }
}

public function destroy($id)
{
    // Find the payment by its ID
    $payment = Payment::find($id);
    
    // Check if the payment exists
    if ($payment !== null) {
        // Delete the payment
        $payment->delete();
        
        // Return a success response
        return response()->json(['message' => 'Payment deleted successfully']);
    } else {
        // Return an error response if the payment does not exist
        return response()->json(['message' => 'Payment not found'], 404);
    }
}




}
