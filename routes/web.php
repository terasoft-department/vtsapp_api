<?php

use Illuminate\Support\Facades\Route;
use App\Models\Assignment;
use App\Mail\NewAssignmentMail;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\AssignmentController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/', [AssignmentController::class, 'showAssignment']);

Route::get('/send-assignment-email/{assignmentId}', function ($assignmentId) {
    // Fetch the assignment based on the ID
    $assignment = Assignment::findOrFail($assignmentId);

    // Send the email to the user
    Mail::to($assignment->user->email)->send(new NewAssignmentMail($assignment));

    return 'Email sent successfully';
});
