<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\TransactionController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return new UserResource($request->user());
    });
    Route::post('/logout', [LoginController::class, 'logout']);

    // User routes
    Route::get('/users', function (Request $request) {
        $users = \App\Models\User::where('id', '!=', $request->user()->id)
            ->where('is_admin', false)
            ->select('id', 'uid', 'name', 'email')
            ->get();

        return UserResource::collection($users);
    });

    // Search users by email or name
    Route::get('/users/search', function (Request $request) {
        $query = $request->input('q');

        if (!$query || strlen($query) < 2) {
            return response()->json([]);
        }

        $users = \App\Models\User::where('id', '!=', $request->user()->id)
            ->where('is_admin', false)
            ->where(function($q) use ($query) {
                $q->where('email', 'like', '%' . $query . '%')
                  ->orWhere('name', 'like', '%' . $query . '%')
                  ->orWhere('uid', 'like', '%' . $query . '%');
            })
            ->select('id', 'uid', 'name', 'email')
            ->limit(10)
            ->get();

        return UserResource::collection($users);
    });

    // Transaction routes
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
});
