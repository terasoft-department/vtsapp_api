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



