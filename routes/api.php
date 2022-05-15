<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\HomeController;

Route::group(['prefix' => 'users'], function ($router) {
    Route::get('/', [UserController::class, 'userIndex']);
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);

    Route::group(['middleware' => ['auth:api']], function(){
        Route::post('/create-student', [UserController::class, 'createStudent']);
        Route::get('/me', [UserController::class, 'viewProfile']);
        Route::get('/dashboard', [UserController::class, 'getDashboard']);
        Route::get('/logout', [UserController::class, 'logout']);
    });
});

Route::get('/categories',[HomeController::class,'categories']);
Route::get('/courses',[CourseController::class,'index']);

Route::group(['middleware' => ['auth:api']], function(){

    Route::get('/wishlist', [UserController::class, 'wishlist']);
    Route::get('/wishlist/student/{user_id}', [UserController::class, 'childWishlist']);
    Route::post('/wishlist/add/{course_id}', [UserController::class, 'addToWishlist']);
    Route::post('/wishlist/remove/{course_id}', [UserController::class, 'removeFromWishlist']);
    
    // COURSE
    Route::get('/courses/manage', [CourseController::class, 'createdCourses']);
    Route::get('/courses/{course_id}/details', [CourseController::class, 'courseDetails']);
    Route::get('/courses/{course_id}/assign-info', [CourseController::class, 'courseAssignInfo']);
    Route::post('/courses/new', [CourseController::class, 'courseCreate']);
    Route::post('/courses/{course_id}/edit', [CourseController::class, 'courseContentCreate']);
    Route::post('/courses/{course_id}/delete', [CourseController::class, 'courseDelete']);
    Route::post('/courses/{course_id}/modules/new', [CourseController::class, 'courseContentCreate']);
    Route::post('/courses/modules/{module_id}/edit', [CourseController::class, 'courseContentEdit']);
    Route::post('/courses/modules/{module_id}/delete', [CourseController::class, 'courseContentCreate']);

    Route::post('/modules/{module_id}/quiz/new', [CourseController::class, 'QuizCreate']);
    Route::post('/quiz/{quiz_id}/questions/new', [CourseController::class, 'QuizCreate']);

    Route::post('/courses/{course_id}/purchase', [CourseController::class, 'coursePurchase']);
    Route::post('/courses/{course_id}/assign', [CourseController::class, 'courseAssign']);
    Route::post('/courses/{course_id}/enroll', [CourseController::class, 'courseEnroll']);

    // CATEGORY
    Route::post('/categories/new',[HomeController::class,'categoryStore']);
});