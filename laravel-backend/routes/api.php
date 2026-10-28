<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

//  AUTH ----------------------
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Sanctum-protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/user/profile-picture', [UserController::class, 'updateProfilePicture']);
    Route::post('/users/block', [UserController::class, 'blockUser']);
    Route::post('/users/unblock', [UserController::class, 'unblockUser']);


    //  CHATS ----------------------
    Route::get('/chats', [ChatController::class, 'index']);
    Route::post('/chats', [ChatController::class, 'store']);
    Route::get('/chats/{chat}', [ChatController::class, 'show']);
    Route::post('/chats/{chat}/join', [ChatController::class, 'join']);
    Route::delete('/chats/{chat}/leave', [ChatController::class, 'leave']);
    Route::post('/chats/{chat}/add-user', [ChatController::class, 'addUserByEmail']);

    // MESSAGES ----------------------
    Route::get('/chats/{chat}/messages', [MessageController::class, 'index']);
    Route::post('/chats/{chat}/messages', [MessageController::class, 'store']);
    Route::post('/messages/{message}/read', [MessageController::class, 'markAsRead']);
    Route::delete('/chats/{chat}/messages/{message}', [MessageController::class, 'destroy']);
    //  USERS ----------------------
    Route::get('/users', [UserController::class, 'index']); // search users
    Route::get('/users/{user}', [UserController::class, 'show']);
});
