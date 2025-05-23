<?php

use Illuminate\Support\Facades\Route;
use App\Services\DynamoDbService;

use App\Models\User;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

/**
 * Routes added below to manage the AWS Cognito change in case you are
 * using Laravel Scafolling
 */

Route::get('/login', function () { return view('auth.login'); })->name('login');
Route::get('/login/mfa', function () { return view('auth.login_mfa_code'); })->name('cognito.form.mfa.code');
Route::post('/login/mfa', [App\Http\Controllers\WebMFAController::class, 'actionValidateMFA'])->name('cognito.form.mfa.code');
Route::get('/register', function () { return view('auth.register'); })->name('register');
Route::get('/password/forgot', function () { return view('auth.passwords.email'); })->name('password.request');
Route::get('/password/reset', function () { return view('auth.passwords.reset'); })->name('cognito.form.reset.password.code');

Route::middleware('aws-cognito')->get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::middleware('aws-cognito')->get('/password/change', function () { return view('auth.passwords.change'); })->name('cognito.form.change.password');
Route::middleware('aws-cognito')->post('/password/change', [App\Http\Controllers\Auth\ChangePasswordController::class, 'actionChangePassword'])->name('cognito.action.change.password');

Route::middleware('aws-cognito')->get('/mfa/enable', [App\Http\Controllers\WebMFAController::class, 'actionEnableMFA'])->name('cognito.action.mfa.enable');
Route::middleware('aws-cognito')->get('/mfa/disable', [App\Http\Controllers\WebMFAController::class, 'actionDisableMFA'])->name('cognito.action.mfa.disable');
Route::middleware('aws-cognito')->get('/mfa/activate', [App\Http\Controllers\WebMFAController::class, 'actionActivateMFA'])->name('cognito.action.mfa.activate');
Route::middleware('aws-cognito')->post('/mfa/verify', [App\Http\Controllers\WebMFAController::class, 'actionVerifyMFA'])->name('cognito.action.mfa.verify');

Route::middleware('aws-cognito')->any('logout', function (\Illuminate\Http\Request $request) {
    Auth::guard()->logout();
    return redirect('/');
})->name('logout');
Route::middleware('aws-cognito')->any('logout/forced', function (\Illuminate\Http\Request $request) {
    Auth::guard()->logout(true);
    return redirect('/');
})->name('logout_forced');

Route::get('/admin/users', [App\Http\Controllers\AdminController::class, 'listUsers']);

Route::get('/admin/seed-dynamo', function (DynamoDbService $dynamo) {
    $user = new User([
        'id' => 'abc-123',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'sub' => 'abc-123',
    ]);

    Auth::login($user);

    $dynamo->putUserItem([
        'UserId' => $user->sub,
        'email' => $user->email,
        'role' => 'admin',
    ]);

    return 'Seeded!';
});
