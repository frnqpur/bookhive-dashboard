<?php

use App\Http\Controllers\Client\ClientApiController;
use App\Http\Controllers\System\HealthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/health', HealthController::class);

Route::group([
    'as' => 'client.',
    'prefix' => 'client',
], function () {
    Route::post('/register', [ClientApiController::class, 'register'])->name('register');
    Route::post('/login', [ClientApiController::class, 'login'])->name('login');

    Route::get('/books', [ClientApiController::class, 'books'])->name('books.index');
    Route::get('/books/{book}', [ClientApiController::class, 'showBook'])->name('books.show');
    Route::get('/books/{book}/reviews', [ClientApiController::class, 'bookReviews'])->name('books.reviews');

    // Backward-compatible aliases from the original practice project.
    Route::get('/book/{book_slug}', [ClientApiController::class, 'getSingleBookData'])->name('getSingleBookData');
    Route::get('/book/{book_slug}/bookReviews', [ClientApiController::class, 'singleBookReviews'])->name('singleBookReviews');
    Route::post('/getJwtToken', [ClientApiController::class, 'getJwtToken'])->name('getJwtToken');

    Route::middleware('jwt.verify')->group(function () {
        Route::post('/logout', [ClientApiController::class, 'logout'])->name('logout');
        Route::get('/me', [ClientApiController::class, 'me'])->name('me');
        Route::get('/user', [ClientApiController::class, 'me'])->name('user');
        Route::get('/my-reviews', [ClientApiController::class, 'myReviews'])->name('myReviews');
        Route::post('/books/{book}/reviews', [ClientApiController::class, 'storeBookReview'])->name('books.reviews.store');
        Route::post('/reviews', [ClientApiController::class, 'createBookReview'])->name('reviews.store');
        Route::match(['put', 'patch'], '/reviews/{review}', [ClientApiController::class, 'updateReview'])->name('reviews.update');
        Route::delete('/reviews/{review}', [ClientApiController::class, 'deleteReview'])->name('reviews.destroy');

        // Backward-compatible aliases. Keep protected and permission-aware.
        Route::post('/createBook', [ClientApiController::class, 'createBook'])->name('createBook');
        Route::post('/createBookReview', [ClientApiController::class, 'createBookReview'])->name('createBookReview');
    });
});
