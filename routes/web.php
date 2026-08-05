<?php

use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Be\ApiController;
use App\Http\Controllers\Be\BookReviewsController;
use App\Http\Controllers\Be\BooksController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ApiDocsController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DemoResetController;
use App\Http\Controllers\Global\GlobalSettingController;
use App\Http\Controllers\Global\PermissionController;
use App\Http\Controllers\Global\ProfileController;
use App\Http\Controllers\Global\RoleController;
use App\Http\Controllers\Global\UserController;
use App\Http\Controllers\PublicSite\PublicPageController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', HomeController::class)->name('home');

Route::get('/about-demo', [PublicPageController::class, 'aboutDemo'])->name('about-demo');
Route::get('/contact-developer', [PublicPageController::class, 'contactDeveloper'])->name('contact-developer');

Route::group([
    'prefix' => '/dashboard',
    'middleware' => ['auth', 'verified'],
], function () {
    Route::get('/', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    Route::group(['as' => 'dashboard.'], function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->middleware('permission:profile.update-own')->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::put('/password', [PasswordController::class, 'update'])->middleware('permission:profile.update-own')->name('password.update');


        Route::get('/audit-logs', [AuditLogController::class, 'index'])
            ->middleware('permission:audit-logs.view')
            ->name('auditLogs.index');

        Route::get('/api-docs', [ApiDocsController::class, 'index'])
            ->middleware('permission:api-docs.access')
            ->name('apiDocs.index');

        Route::get('/demo-reset', [DemoResetController::class, 'index'])
            ->middleware(['permission:demo-reset.manage', 'role:Super Admin'])
            ->name('demoReset.index');
        Route::post('/demo-reset', [DemoResetController::class, 'run'])
            ->middleware(['permission:demo-reset.manage', 'role:Super Admin'])
            ->name('demoReset.run');

        Route::get('/globalSettings', [GlobalSettingController::class, 'edit'])
            ->middleware('permission:settings.manage')
            ->name('globalSettings.edit');
        Route::patch('/globalSettings', [GlobalSettingController::class, 'update'])
            ->middleware('permission:settings.manage')
            ->name('globalSettings.update');

        Route::group(['as' => 'global.', 'prefix' => '/global'], function () {
            Route::group(['as' => 'users.', 'prefix' => 'users', 'middleware' => 'permission:users.manage'], function () {
                Route::get('/list', [UserController::class, 'list'])->name('list');
                Route::get('/create', [UserController::class, 'create'])->name('create');
                Route::get('/edit/{id}', [UserController::class, 'edit'])->whereNumber('id')->name('edit');
                Route::patch('/storeUpdate/{id?}', [UserController::class, 'storeUpdate'])->whereNumber('id')->name('storeUpdate');
                Route::delete('/remove/{id}', [UserController::class, 'remove'])->whereNumber('id')->name('remove');
            });

            Route::group(['as' => 'permissions.', 'prefix' => 'permissions', 'middleware' => 'permission:permissions.manage'], function () {
                Route::get('/list', [PermissionController::class, 'list'])->name('list');
                Route::get('/create', [PermissionController::class, 'create'])->name('create');
                Route::get('/edit/{id}', [PermissionController::class, 'edit'])->whereNumber('id')->name('edit');
                Route::patch('/storeUpdate/{id?}', [PermissionController::class, 'storeUpdate'])->whereNumber('id')->name('storeUpdate');
                Route::delete('/remove/{id}', [PermissionController::class, 'remove'])->whereNumber('id')->name('remove');
            });

            Route::group(['as' => 'roles.', 'prefix' => 'roles', 'middleware' => 'permission:roles.manage'], function () {
                Route::get('/list', [RoleController::class, 'list'])->name('list');
                Route::get('/create', [RoleController::class, 'create'])->name('create');
                Route::get('/edit/{id}', [RoleController::class, 'edit'])->whereNumber('id')->name('edit');
                Route::patch('/storeUpdate/{id?}', [RoleController::class, 'storeUpdate'])->whereNumber('id')->name('storeUpdate');
                Route::delete('/remove/{id}', [RoleController::class, 'remove'])->whereNumber('id')->name('remove');
            });
        });

        Route::group(['as' => 'be.', 'prefix' => '/be'], function () {
            Route::group(['as' => 'books.', 'prefix' => 'books'], function () {
                Route::get('/list', [BooksController::class, 'list'])->middleware('permission:books.view|books.manage')->name('list');
                Route::get('/create', [BooksController::class, 'create'])->middleware('permission:books.manage')->name('create');
                Route::get('/show/{id}', [BooksController::class, 'show'])->middleware('permission:books.view|books.manage')->whereNumber('id')->name('show');
                Route::get('/{book}/reviews/create', [BookReviewsController::class, 'createForBook'])->middleware('permission:reviews.create|reviews.manage')->whereNumber('book')->name('reviews.create');
                Route::get('/edit/{id}', [BooksController::class, 'edit'])->middleware('permission:books.manage')->whereNumber('id')->name('edit');
                Route::patch('/storeUpdate/{id?}', [BooksController::class, 'storeUpdate'])->middleware('permission:books.manage')->whereNumber('id')->name('storeUpdate');
                Route::delete('/remove/{id}', [BooksController::class, 'remove'])->middleware('permission:books.manage')->whereNumber('id')->name('remove');
            });

            Route::group(['as' => 'bookReviews.', 'prefix' => 'bookReviews'], function () {
                Route::get('/list', [BookReviewsController::class, 'list'])->middleware('permission:reviews.view|reviews.manage|reviews.approve')->name('list');
                Route::get('/my', [BookReviewsController::class, 'myReviews'])->middleware('permission:reviews.view|reviews.create|reviews.update-own|reviews.manage|reviews.approve')->name('my');
                Route::get('/moderation', [BookReviewsController::class, 'moderationQueue'])->middleware('permission:reviews.approve')->name('moderation');
                Route::get('/moderation/{id}', [BookReviewsController::class, 'moderationPage'])->middleware('permission:reviews.approve')->whereNumber('id')->name('moderation.show');
                Route::get('/create', [BookReviewsController::class, 'create'])->middleware('permission:reviews.create|reviews.manage')->name('create');
                Route::get('/edit/{id}', [BookReviewsController::class, 'edit'])->middleware('permission:reviews.update-own|reviews.manage|reviews.approve')->whereNumber('id')->name('edit');
                Route::patch('/storeUpdate/{id?}', [BookReviewsController::class, 'storeUpdate'])->middleware('permission:reviews.create|reviews.update-own|reviews.manage|reviews.approve')->whereNumber('id')->name('storeUpdate');
                Route::patch('/moderate/{id}', [BookReviewsController::class, 'moderate'])->middleware('permission:reviews.approve')->whereNumber('id')->name('moderate');
                Route::delete('/remove/{id}', [BookReviewsController::class, 'remove'])->middleware('permission:reviews.update-own|reviews.manage')->whereNumber('id')->name('remove');
            });
        });
    });

    Route::group(['as' => 'fetch.', 'prefix' => '/fetch'], function () {
        Route::get('/users', [ApiController::class, 'users'])->middleware('permission:users.manage')->name('users');
        Route::get('/permissions', [ApiController::class, 'permissions'])->middleware('permission:permissions.manage')->name('permissions');
        Route::get('/roles', [ApiController::class, 'roles'])->middleware('permission:roles.manage')->name('roles');
        Route::get('/books', [ApiController::class, 'books'])->middleware('permission:books.view|books.manage')->name('books');
        Route::get('/bookReviews', [ApiController::class, 'bookReviews'])->middleware('permission:reviews.view|reviews.manage|reviews.approve')->name('bookReviews');
    });
});

require __DIR__ . '/auth.php';
