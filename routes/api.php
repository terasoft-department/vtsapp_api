<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\DeviceRequisitionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\InstallationController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\JobCardController;
use App\Http\Controllers\DeviceReturnController;
use App\Http\Controllers\JobCardAttachmentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\VehicleRegistrationController;
use App\Http\Controllers\CheckListController;

// Public Routes
Route::get('/login', function () {
    return response()->json(['message' => 'Unauthorided user!!, Please!!! login to access the api'], 401);
})->name('login');

// Authentication Routes
Route::post('/register_v1', [AuthController::class, 'register']);
Route::post('/login_v1', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/get_login_user', [AuthController::class, 'getLoggedUserName']);
    Route::get('/user/profile', [AuthController::class, 'getLoggedUserProfile']);
     Route::get('/user_logged_user_id', [AuthController::class, 'getLoggedUserID']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
     Route::apiResource('roles', RoleController::class);


    // Assignment Routes
    Route::apiResource('assignments', AssignmentController::class);
    Route::get('/assignmentsv1', [AssignmentController::class, 'index1']);
     Route::get('/fetchcustomer', [AssignmentController::class, 'fetchcustomer']);
    Route::get('/countAssign', [AssignmentController::class, 'countAssignments']);
      Route::post('/acceptReject/{assignmentId}', [AssignmentController::class, 'acceptReject']);

    //device requisitions routes
    Route::apiResource('device-requisitions', DeviceRequisitionController::class);
    Route::get('/stocks', [DeviceRequisitionController::class, 'index1']);
     Route::get('/countRequisitions', [ DeviceRequisitionController::class, 'countRequisitions']);

// Route for counting master devices
Route::get('/count/master', [DeviceRequisitionController::class, 'countMaster']);
// Route for counting I_button devices
Route::get('/count/i_button', [DeviceRequisitionController::class, 'countI_button']);
// Route for counting buzzer devices
Route::get('/count/buzzer', [DeviceRequisitionController::class, 'countBuzzer']);
// Route for counting panick_button devices
Route::get('/count/panick_button', [DeviceRequisitionController::class, 'countPanick_button']);
// Route for counting the total number of devices (all categories)
Route::get('/count/total', [DeviceRequisitionController::class, 'countTotalDevices']);


    //customer Route
     Route::apiResource('/customers', CustomerController::class);

     //jobcard routes
Route::apiResource('jobcards', JobCardController::class);
Route::get('/countJobCards', [ JobCardController::class, 'countJobCards']);
Route::get('/fetchvehico_regNo', [ JobCardController::class, 'fetchvehico_regNo']);


//device return
 Route::post('/device-return/filter', [DeviceReturnController::class, 'filterByPlateNumber']);
 Route::post('/device-return/store', [DeviceReturnController::class, 'store']);
 Route::get('/device-return/all', [DeviceReturnController::class, 'getAllReturns']);

//job card att
Route::apiResource('attachments', JobCardAttachmentController::class);

//vehicle route
Route::apiResource('vehicles', VehicleRegistrationController::class);
// For filtering by plate number
Route::post('vehicles/filter', [VehicleRegistrationController::class, 'filter']);

// Check list routes
Route::post('/checklist/auto-fill', [CheckListController::class, 'autoFillDetails']);
Route::post('/checklist/submit', [CheckListController::class, 'submitChecklist']);
 Route::get('/checklists/count', [CheckListController::class, 'countCheckLists']);
  Route::get('/checklists/status/{status}', [CheckListController::class, 'indexByStatus']);
    Route::post('/filter-by-date', [CheckListController::class, 'filterChecklistByDate']);
});

