import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart'; // For storing token
import 'package:intl/intl.dart'; // For date formatting

class CheckLists extends StatefulWidget {
  @override
  _CheckListsState createState() => _CheckListsState();
}

class _CheckListsState extends State<CheckLists> {
  final TextEditingController _plateNumberController = TextEditingController();
  final TextEditingController _checkDateController = TextEditingController();
  final FlutterSecureStorage storage = FlutterSecureStorage(); // Secure storage for token

  String? _customerId;
  String? _customerName;
  String? _vehicleId;
  String? _rbtStatus = 'ver_good'; // Default status
  String? _battStatus = 'ver_good'; // Default status
  bool isLoading = false;
  DateTime? _selectedDate;

  final List<String> rbtOptions = ['ver_good', 'good', 'moderate', 'poor'];
  final List<String> battOptions = ['ver_good', 'good', 'moderate', 'poor'];

  // Method to search for vehicle and auto-fill details
  Future<void> _searchVehicle() async {
    final plateNumber = _plateNumberController.text.trim();
    if (plateNumber.isEmpty) {
      _showToast("Please enter a plate number");
      return;
    }

    setState(() {
      isLoading = true; // Show loading indicator
    });

    try {
      final token = await storage.read(key: 'token'); // Read the token from storage
      if (token == null) {
        _showToast("User not authenticated");
        return;
      }

      final response = await http.post(
        Uri.parse('http://127.0.0.1:8000/api/checklist/auto-fill'),
        headers: {
          'Authorization': 'Bearer $token', // Authenticated request
          'Content-Type': 'application/json',
        },
        body: json.encode({'plate_number': plateNumber}),
      );

      if (response.statusCode == 200) {
        final jsonResponse = json.decode(response.body);
        if (jsonResponse['status'] == 'success') {
          setState(() {
            _customerId = jsonResponse['customer_id'].toString();
            _customerName = jsonResponse['customername'];
            _vehicleId = jsonResponse['vehicle_id'].toString();
          });
          _showToast("Vehicle details fetched successfully");
        } else {
          _showToast("Vehicle not found for the number");
        }
      } else {
        _handleError(response);
      }
    } catch (error) {
      _showToast("An error occurred: $error");
    } finally {
      setState(() {
        isLoading = false; // Hide loading indicator
      });
    }
  }

  // Method to select date
  Future<void> _selectDate(BuildContext context) async {
    final DateTime? pickedDate = await showDatePicker(
      context: context,
      initialDate: DateTime.now(),
      firstDate: DateTime(2000),
      lastDate: DateTime(2101),
    );

    if (pickedDate != null && pickedDate != _selectedDate) {
      setState(() {
        _selectedDate = pickedDate;
        _checkDateController.text = DateFormat('yyyy-MM-dd').format(pickedDate); // Format date for backend
      });
    }
  }

  // Method to submit the form
  Future<void> _submitForm() async {
    if (_customerId == null || _vehicleId == null) {
      _showToast("Please search for a vehicle first.");
      return;
    }

    try {
      final token = await storage.read(key: 'token');
      if (token == null) {
        _showToast("User not authenticated");
        return;
      }

      final response = await http.post(
        Uri.parse('http://127.0.0.1:8000/api/checklist/submit'),
        headers: {
          'Authorization': 'Bearer $token',
          'Content-Type': 'application/json',
        },
        body: json.encode({
          'vehicle_id': _vehicleId,
          'customer_id': _customerId,
          'plate_number': _plateNumberController.text,
          'rbt_status': _rbtStatus,
          'batt_status': _battStatus,
          'check_date': _checkDateController.text,
        }),
      );

      if (response.statusCode == 201) {
        _showToast("Checklist submitted successfully!");
      } else {
        _handleError(response);
      }
    } catch (error) {
      _showToast("An error occurred: $error");
    }
  }

  // Error handling
  void _handleError(http.Response response) {
    if (response.statusCode == 404) {
      _showToast("Vehicle or customer not found");
    } else if (response.statusCode == 422) {
      _showToast("Validation error");
    } else {
      _showToast("An error occurred. Please try again later.");
    }
  }

  // Show toast method
  void _showToast(String message) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message)));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        backgroundColor: Colors.blue,
        title: Text('CheckList'),
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          children: [
            TextField(
              controller: _plateNumberController,
              decoration: InputDecoration(
                labelText: 'Enter Plate Number',
                border: OutlineInputBorder(),
              ),
            ),
            SizedBox(height: 16),
            ElevatedButton(
              onPressed: isLoading ? null : _searchVehicle,
              child: isLoading
                  ? CircularProgressIndicator(color: Colors.white)
                  : Text('Search Vehicle'),
            ),
            SizedBox(height: 16),
            if (_customerName != null) ...[
              Text('Customer Name: $_customerName'),
              SizedBox(height: 16),
            ],
            TextField(
              controller: _checkDateController,
              decoration: InputDecoration(
                labelText: 'Check Date',
                suffixIcon: IconButton(
                  icon: Icon(Icons.calendar_today),
                  onPressed: () => _selectDate(context),
                ),
              ),
              readOnly: true,
            ),
            SizedBox(height: 16),
            DropdownButtonFormField<String>(
              value: _rbtStatus,
              decoration: InputDecoration(labelText: 'RBT Status'),
              items: rbtOptions.map((status) {
                return DropdownMenuItem(
                  value: status,
                  child: Text(status),
                );
              }).toList(),
              onChanged: (value) {
                setState(() {
                  _rbtStatus = value;
                });
              },
            ),
            SizedBox(height: 16),
            DropdownButtonFormField<String>(
              value: _battStatus,
              decoration: InputDecoration(labelText: 'BATT Status'),
              items: battOptions.map((status) {
                return DropdownMenuItem(
                  value: status,
                  child: Text(status),
                );
              }).toList(),
              onChanged: (value) {
                setState(() {
                  _battStatus = value;
                });
              },
            ),
            SizedBox(height: 16),
            ElevatedButton(
              onPressed: _submitForm,
              child: isLoading
                  ? CircularProgressIndicator()
                  : Text('Submit Checklist'),
            ),
          ],
        ),
      ),
    );
  }
}



<?php

namespace App\Http\Controllers;

use App\Models\CheckList;
use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CheckListController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function autoFillDetails(Request $request)
{
    // Validate the input for plate_number
    $validator = Validator::make($request->all(), [
        'plate_number' => 'required|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation error',
            'errors' => $validator->errors(),
        ], 422);
    }

    // Search for the vehicle using the plate_number
    $vehicle = Vehicle::where('plate_number', $request->plate_number)->first();
    if (!$vehicle) {
        return response()->json([
            'status' => 'error',
            'message' => 'Vehicle not found for the provided plate number.',
        ], 404);
    }

    // Find the customer associated with the vehicle
    $customer = Customer::find($vehicle->customer_id);
    if (!$customer) {
        return response()->json([
            'status' => 'error',
            'message' => 'Customer not found for the vehicle.',
        ], 404);
    }

    // Return the vehicle and customer_id
    return response()->json([
        'status' => 'success',
        'plate_number' => $vehicle->plate_number,
        'customer_id' => $customer->customer_id,
         'vehicle_id' => $vehicle->vehicle_id,
        'customername' => $customer->customername, // Assuming there's a name field
    ], 200);
}

  public function submitChecklist(Request $request)
{
    $request->validate([
        'vehicle_id' => 'required',
        'customer_id' => 'required',
        'rbt_status' => 'required|string',
        'batt_status' => 'required|string',
        'check_date' => 'required|date',
    ]);

    $checkList = new CheckList();
    $checkList->user_id = auth()->id(); // Authenticated user
    $checkList->vehicle_id = $request->vehicle_id;
    $checkList->customer_id = $request->customer_id;
    $checkList->plate_number = $request->plate_number;
    $checkList->rbt_status = $request->rbt_status;
    $checkList->batt_status = $request->batt_status;
    $checkList->check_date = $request->check_date;
    $checkList->save();

    return response()->json([
        'status' => 'success',
        'message' => 'Checklist submitted successfully!',
    ], 201);
}




}












namespace App\Http\Controllers;

use App\Models\JobCard;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class JobCardController extends Controller
{


public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('cloudinary.cloud.cloud_name'),
                'api_key' => config('cloudinary.cloud.api_key'),
                'api_secret' => config('cloudinary.cloud.api_secret'),
            ],
            'url' => [
                'secure' => true, // Use HTTPS
            ],
        ]);
    }

    public function index()
    {
        try {
            $jobCards = JobCard::where('user_id', Auth::id())
                ->orderBy('jobcard_id', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'job_cards' => $jobCards,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching job cards: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch job cards',
            ], 500);
        }
    }

  public function fetchvehico_regNo()
{
    try {
        // Fetch job cards with only the 'vehicle_regno' attribute
        $jobCards = JobCard::where('user_id', Auth::id())
            ->orderBy('jobcard_id', 'desc')
            ->get(['vehicle_regno']); // Select only the 'vehicle_regno' field

        return response()->json([
            'status' => 'success',
            'job_cards' => $jobCards,
        ], 200);
    } catch (\Exception $e) {
        Log::error('Error fetching job cards: ' . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to fetch job cards',
        ], 500);
    }
}


   public function store(Request $request)
{
    // Step 1: Validate the incoming request
    $validator = Validator::make($request->all(), [
        'customer_id' => 'nullable',
        'assignment_id' => 'required',
        'contact_person' => 'nullable|string|max:255',
        'title' => 'nullable|string|max:255',
        'mobile_number' => 'nullable|string|max:20',
        'physical_location' => 'nullable|string|max:255',
        'vehicle_regno' => 'nullable|string|max:20',
        'problem_reported' => 'nullable|string',
        'natureOf_ProblemAt_site' => 'nullable|string',
        'service_type' => 'nullable|string',
        'date_attended' => 'nullable|date',
        'work_done' => 'nullable|string',
        'client_comment' => 'nullable|string',
        // Ensure images are optional
        'pre_workdone_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'post_workdone_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'carPlateNumber_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'tampering_evidence_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    // Step 2: Check for validation failures
    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation error',
            'errors' => $validator->errors(),
        ], 422);
    }

    // Step 3: Upload images to Cloudinary
    try {
        $uploadedImages = $this->uploadImages($request);
    } catch (\Exception $e) {
        Log::error('Image upload failed: ' . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => 'Image upload failed',
        ], 500);
    }

    // Step 4: Create a new job card
    try {
        $jobCard = JobCard::create(array_merge($request->all(), [
            'user_id' => Auth::id(), // Associate job card with logged-in user
            // Include uploaded image URLs if available
            'pre_workdone_picture' => $uploadedImages['pre_workdone_picture'] ?? null,
            'post_workdone_picture' => $uploadedImages['post_workdone_picture'] ?? null,
            'carPlateNumber_picture' => $uploadedImages['carPlateNumber_picture'] ?? null,
            'tampering_evidence_picture' => $uploadedImages['tampering_evidence_picture'] ?? null,
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Job card created successfully',
            'job_card' => $jobCard,
        ], 201);
    } catch (\Exception $e) {
        Log::error('Error creating job card: ' . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to create job card',
        ], 500);
    }
}

 private function uploadImages(Request $request)
    {
        $uploadedImages = [];

        // Handle each image upload
        foreach (['pre_workdone_picture', 'post_workdone_picture', 'carPlateNumber_picture', 'tampering_evidence_picture'] as $imageField) {
            if ($request->hasFile($imageField)) {
                $file = $request->file($imageField);

                // Upload to Cloudinary
                $uploadResult = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
                    'folder' => 'job_cards', // Optional: Set a folder for organization
                ]);

                // Store the image URL
                $uploadedImages[$imageField] = $uploadResult['secure_url'];
            }
        }

        return $uploadedImages;
    }

// JobCardController.php
public function show($id)
{
    try {
        $jobCard = JobCard::where('jobcard_id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$jobCard) {
            return response()->json([
                'status' => 'error',
                'message' => 'Job card not found or does not belong to the logged-in user',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'job_card' => $jobCard,
        ], 200);
    } catch (\Exception $e) {
        Log::error('Error fetching job card: ' . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to fetch job card',
        ], 500);
    }
}

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'customername' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'mobile_number' => 'nullable|string|max:20',
            'physical_location' => 'nullable|string|max:255',
            'device_id' => 'nullable|exists:devices,device_id',
            'problem_reported' => 'nullable|string',
            'natureOf_ProblemAt_site' => 'nullable|string',
            'service_type' => 'nullable|string',
            'date_attended' => 'nullable|date',
            'work_done' => 'nullable|string',
            'vehicle_regno' => 'nullable|string|max:20',
            'client_comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $jobCard = JobCard::where('jobcard_id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$jobCard) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Job card not found',
                ], 404);
            }

            $jobCard->update($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Job card updated successfully',
                'job_card' => $jobCard,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating job card: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update job card',
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $jobCard = JobCard::where('jobcard_id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$jobCard) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Job card not found',
                ], 404);
            }

            $jobCard->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Job card deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting job card: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete job card',
            ], 500);
        }
    }

    public function countJobCards()
{
    try {
        // Count the total number of job cards for the logged-in user
        $count = JobCard::where('user_id', Auth::id())
            ->count();

        // Return the count as JSON
        return response()->json([
            'status' => 'success',
            'count' => $count,
        ], 200);
    } catch (\Exception $e) {
        // Log the error message
        Log::error('Error counting job cards: ' . $e->getMessage());

        // Return an error response
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to count job cards',
        ], 500);
    }
}

}

