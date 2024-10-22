<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['register', 'login']);
    }

    public function register(Request $request)
    {
        // Check if email already exists
        if (User::where('email', $request->email)->exists()) {
            // Set session message for account existence
            Session::flash('message', 'An account with this email already exists.');
            return response()->json(['message' => 'An account with this email already exists.'], 409);
        }

        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'status' => 'required|nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8', // Added confirmed rule
        ]);

        // Hash the password
        $validatedData['password'] = Hash::make($validatedData['password']);

        // Create the user
        $user = User::create($validatedData);

        // Return a success response
        return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
    }



   public function login(Request $request)
{
    // Validate the incoming request data
    $credentials = $request->validate([
        'email' => 'required|string|email',
        'password' => 'required|string|min:8',
    ]);

    // Attempt to authenticate the user
    if (!Auth::attempt($credentials)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    try {
        // Retrieve the authenticated user
        $user = Auth::user();

        // Check if the user is active
        if ($user->status !== 'is_active') {
            return response()->json(['message' => 'Your account is inactive. Please contact support.'], 403);
        }

        // Generate an authentication token
        $token = $user->createToken('authToken')->plainTextToken;

        // Return the user and token in the response
        return response()->json(['user' => $user, 'token' => $token], 200);
    } catch (\Exception $e) {
        // Handle any exceptions that occur
        return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
    }
}




    public function logout(Request $request)
    {
        $request->user()->tokens()->delete(); // Invalidate all tokens for the user

        return response()->json(['message' => 'Logged out']);
    }

  public function getLoggedUserProfile(Request $request)
{
    $user = $request->user();


    return response()->json([
        'user_id' => $user->user_id,
        'email' => $user->email,
        'name' => $user->name,
        'role' => $user->role,
        'status' => $user->status
    ]);
}



    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'No user found with this email address.'], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'Password has been reset successfully.']);
    }

    public function getLoggedUserName(Request $request)
{
    $user = $request->user();

    // Assuming 'name' is the column in your users table
    return response()->json(['name' => $user->name]);
}



     public function getLoggedUserID(Request $request)
    {
        $user = $request->user();

        return response()->json(['user_id' => $user->user_id]);
    }
}

