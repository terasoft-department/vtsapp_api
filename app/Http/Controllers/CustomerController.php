<?php


namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    // Fetch all customers
    public function index()
    {
        $customers = Customer::all();
        return response()->json($customers);
    }

    // Fetch a specific customer by ID
    public function show($id)
    {
        $customer = Customer::find($id);

        if ($customer) {
            return response()->json($customer);
        } else {
            return response()->json(['message' => 'Customer not found'], 404);
        }
    }

    // Create a new customer
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customername' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'TinNumber' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'start_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $customer = Customer::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Customer created successfully',
            'customer' => $customer,
        ], 201);
    }

    // Update an existing customer
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'customername' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'TinNumber' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'start_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $customer = Customer::find($id);

        if ($customer) {
            $customer->update($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Customer updated successfully',
                'customer' => $customer,
            ]);
        } else {
            return response()->json(['message' => 'Customer not found'], 404);
        }
    }

    // Delete a customer
    public function destroy($id)
    {
        $customer = Customer::find($id);

        if ($customer) {
            $customer->delete();
            return response()->json(['message' => 'Customer deleted successfully']);
        } else {
            return response()->json(['message' => 'Customer not found'], 404);
        }
    }
}
