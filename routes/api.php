<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - EyesComp SaaS
|--------------------------------------------------------------------------
*/

// Routes publiques d'authentification
Route::prefix('auth')->group(function () {
    Route::post('register-owner', [AuthController::class, 'registerOwner']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('pin-login', [AuthController::class, 'pinLogin']);

    // Routes protégées par Sanctum
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

// Routes protégées de l'application
Route::middleware('auth:sanctum')->group(function () {
    // 1. Module Catégories (F-PROD-02)
    Route::apiResource('categories', CategoryController::class);

    // 2. Module Catalogue Produits (F-PROD-01 à F-PROD-05)
    Route::get('products/search', [ProductController::class, 'search']);
    Route::post('products/{product}/archive', [ProductController::class, 'archive']);
    Route::post('products/{product}/restore', [ProductController::class, 'restore']);
    Route::apiResource('products', ProductController::class);
});
