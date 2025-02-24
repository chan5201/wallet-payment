<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Models\User;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('transactions/{id}', [\App\Http\Controllers\Wallet\TransferController::class, 'getDetailById'])->name('transactions.detail.get');
    Route::post('transfers', [\App\Http\Controllers\Wallet\TransferController::class, 'postTransfer'])->name('transfer.post');
});

Route::get('login', function() {
    return "Invalid user credentials";
})->name('login');
Route::post('/login', function (Request $request) {
    $user = User::where('email', $request->input('email'))->first();

    $token = $user->createToken('member', ['transfer']);

    return ['token' => $token->plainTextToken];
})->name('login.post');


