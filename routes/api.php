<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\CognitoClient;
use App\Services\DynamoDbService;
use App\Models\User;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/test-connection', [App\Http\Controllers\HomeController::class, 'test']);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index']);

Route::prefix('user')->group(function () {
    Route::post('login', [App\Http\Controllers\ApiAuthController::class, 'actionLogin']);
    Route::post('register', [App\Http\Controllers\ApiAuthController::class, 'actionRegister'])->middleware('cors');
    Route::post('/login/mfa', [App\Http\Controllers\ApiMFAController::class, 'actionValidateMFA']);



    Route::group(['middleware' => 'aws-cognito'], function() {
        Route::get('profile', [App\Http\Controllers\AuthController::class, 'getRemoteUser']);
        Route::post('mfa/enable', [App\Http\Controllers\ApiMFAController::class, 'actionApiEnableMFA']);
        Route::post('mfa/disable', [App\Http\Controllers\ApiMFAController::class, 'actionApiDisableMFA']);
        Route::get('mfa/activate', [App\Http\Controllers\ApiMFAController::class, 'actionApiActivateMFA']);
        Route::post('mfa/activate/{code}', [App\Http\Controllers\ApiMFAController::class, 'actionApiVerifyMFA']);
        Route::post('mfa/deactivate', [App\Http\Controllers\ApiMFAController::class, 'actionApiDeactivateMFA']);
        Route::put('logout', function (\Illuminate\Http\Request $request) {
            Auth::guard('api')->logout();
        });
        Route::put('logout/forced', function (\Illuminate\Http\Request $request) {
            Auth::guard('api')->logout(true);
        });
        Route::post('refresh-token', [App\Http\Controllers\ResetController::class, 'actionRefreshToken']);
    });
});

Route::middleware(['auth:cognito'])->group(function () {
    Route::post('/profile', [UserProfileController::class, 'store']);
    Route::get('/profile/{id}', [UserProfileController::class, 'show']);
});

Route::middleware(['aws-cognito', 'check.role:admin'])->group(function(){
    Route::get('/admin/users', [App\Http\Controllers\AdminController::class, 'listUsers']);
});


Route::get('/test-cognito', [App\Http\Controllers\TestController::class, 'testCognito']);
Route::get('/test/admin/users', [App\Http\Controllers\TestController::class, 'listUsers']);

Route::get('/test/admin/seed-dynamo', function (DynamoDbService $dynamo) {
    $user = new User([
        'id' => 'abc-123',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'test123456',
        'sub' => 'abc-123',
    ]);

    Auth::login($user);

    $dynamo->putUserItem([
        'UserId' => $user->sub,
        'email' => $user->email,
        'role' => 'admin',
        'userData' => json_encode($user),
    ]);

    return 'Seeded!';
});
