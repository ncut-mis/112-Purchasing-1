<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Models\Post;
use App\Models\PurchasingRequest;

Route::get('/', function () {
    // 抓取代購貼文
    $posts = Post::all(); 
    
    // 抓取請購清單 (如果還沒做 Model，暫時用 collect([]) 代替)
    $requests = class_exists(PurchasingRequest::class) 
                ? PurchasingRequest::all() 
                : collect([]);

    return view('home', compact('posts', 'requests'));
})->name('home');





Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
