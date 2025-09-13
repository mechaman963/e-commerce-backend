<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\socialAuthController;
use App\Http\Controllers\UsersContoller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public Routes
Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::post('/passowrd', 'sendResetLink');
    Route::post('/reset-password', 'reset');
});

Route::get('/latest-sale', [ProductController::class, 'getLastSaleProducts']);
Route::get('/latest', [ProductController::class, 'getLatest']);
Route::get('/top-rated', [ProductController::class, 'getTopRated']);

// Public rating routes
Route::get('/product/{id}/ratings', [RatingController::class, 'getProductRatings']);
Route::get('/product/{id}/rating-stats', [RatingController::class, 'getProductRatingStats']);

// Public category routes
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/category/{id}', [CategoryController::class, 'show']);

// Public product routes
Route::get('/products', [ProductController::class, 'index']);
Route::get('/product/{id}', [ProductController::class, 'show']);

Route::get('/login-google', [socialAuthController::class, 'redirectToProvider']);
Route::get('/auth/google/callback', [socialAuthController::class, 'handleCallback']);

// Protected Routes
Route::middleware('auth:api')->group(function () {
    // Users
    Route::get('/user', [UsersContoller::class, 'authUser']);
    Route::get('/user/{id}', [UsersContoller::class, 'getUser']); // Allow authenticated users to get their own info
    
    Route::middleware('checkAdmin')->controller(UsersContoller::class)->group(function () {
        Route::get('/users', 'GetUsers');
        Route::post('/user/edit/{id}', 'editUser');
        Route::post('/user/add', 'addUser');
        Route::delete('/user/{id}', 'destroy');
    });
    
    //category (protected routes only)
    Route::middleware('checkProductManager')->controller(CategoryController::class)->group(function () {
        Route::post('/category/edit/{id}', 'edit');
        Route::post('/category/add', 'store');
        Route::delete('/category/{id}', 'destroy');
    });
    //products (protected routes only)
    Route::middleware('checkProductManager')->controller(ProductController::class)->group(function () {
        Route::post('/product/edit/{id}', 'update');
        Route::post('/product/add', 'store');
        Route::delete('/product/{id}', 'destroy');
    });
    Route::middleware('checkProductManager')->controller(ProductImageController::class)->group(function () {
        Route::post('/product-img/add', 'store');
        Route::delete('/product-img/{id}', 'destroy');
    });

    // Ratings (Protected routes)
    Route::post('/rating', [RatingController::class, 'store']);
    Route::get('/product/{id}/user-rating', [RatingController::class, 'getUserRating']);
    Route::delete('/rating/{id}', [RatingController::class, 'destroy']);

    // Cart routes (Protected - requires authentication)
    Route::controller(CartController::class)->group(function () {
        Route::get('/cart', 'index');
        Route::post('/cart', 'store');
        Route::put('/cart/{id}', 'update');
        Route::delete('/cart/{id}', 'destroy');
        Route::delete('/cart', 'clear');
        Route::get('/cart/count', 'count');
    });

    // Auth
    Route::get('/logout', [AuthController::class, 'logout']);
});
