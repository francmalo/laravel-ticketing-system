<?php

use App\Http\Controllers\TicketController;
use App\Http\Controllers\CommentController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
require __DIR__.'/auth.php';

// Ticket Routes
Route::middleware(['auth'])->group(function () {
    // Routes accessible by all authenticated users
    Route::get('/', [TicketController::class, 'index'])->name('home');
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');

    // Routes only for Admin and IT Staff
    Route::middleware(['role:Admin,IT Staff'])->group(function () {
        Route::get('/tickets/{ticket}/edit', [TicketController::class, 'edit'])->name('tickets.edit');
        Route::put('/tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');
        Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])->name('tickets.destroy');
        Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.status');
        Route::patch('/tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
    });



    // Comments route
    Route::post('tickets/{ticket}/comments', [CommentController::class, 'store'])->name('comments.store');
});

