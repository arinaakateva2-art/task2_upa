<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileManagerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/files'); 
});

Route::get('/dashboard', function () {
    return redirect('/files');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::get('/files', [FileManagerController::class, 'index'])->name('files.index');
    Route::post('/files/upload', [FileManagerController::class, 'upload'])->name('files.upload');
    Route::post('/folders', [FileManagerController::class, 'createFolder'])->name('folders.store');
    Route::delete('/folders/{folder}', [FileManagerController::class, 'deleteFolder'])->name('folders.destroy');
    Route::get('/files/{file}/download', [FileManagerController::class, 'download'])->name('files.download');
    Route::delete('/files/{file}', [FileManagerController::class, 'destroy'])->name('files.destroy');
    Route::patch('/files/{file}/rename', [FileManagerController::class, 'rename'])->name('files.rename');
    Route::patch('/files/{file}/move', [FileManagerController::class, 'move'])->name('files.move');
});