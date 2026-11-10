<?php

use App\Http\Controllers\Admin\BookController as AdminBookController;
use App\Http\Controllers\Admin\BookRequestController as AdminBookRequestController;
use App\Http\Controllers\Admin\ChapterController;
use App\Http\Controllers\Admin\ManageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\BookRequestController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReadingController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ShelfController;
use App\Http\Middleware\AdminOnly;
use Illuminate\Support\Facades\Route;

// страницы, которые видят все
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/library', [LibraryController::class, 'index'])->name('library');
Route::get('/library/{book}', [LibraryController::class, 'show'])->name('book');
Route::get('/about', [PageController::class, 'about'])->name('about');


// вход и регистрация, только для тех, кто не вошёл
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');

    // восстановление пароля по письму
    Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])->name('password.email')->middleware('throttle:5,1');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update')->middleware('throttle:10,1');
});

// страницы для тех, кто вошёл в аккаунт
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/shelf', [ShelfController::class, 'index'])->name('shelf');
    Route::post('/shelf/{book}', [ShelfController::class, 'toggle'])->name('shelf.toggle');

    // заявки на книги
    Route::get('/requests', [BookRequestController::class, 'index'])->name('requests');
    Route::post('/requests', [BookRequestController::class, 'store'])->middleware('throttle:10,60')->name('requests.store');

    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms');
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::post('/rooms/join', [RoomController::class, 'join'])->name('rooms.join');
    Route::get('/join/{code}', [RoomController::class, 'joinLink'])->name('join.link');
    Route::post('/rooms/{room}/invite', [RoomController::class, 'invite'])->middleware('throttle:5,1')->name('room.invite');
    Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('room');
    Route::get('/rooms/{room}/notes', [RoomController::class, 'notes'])->name('room.notes');
    Route::patch('/rooms/{room}', [RoomController::class, 'update'])->name('room.update');
    Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('room.destroy');

    Route::post('/rooms/{room}/bookmark', [ReadingController::class, 'bookmark'])->name('room.bookmark');
    Route::post('/rooms/{room}/progress', [ReadingController::class, 'progress'])->name('room.progress');
    Route::post('/rooms/{room}/notes', [ReadingController::class, 'storeNote'])->name('room.notes.store');
    Route::put('/rooms/{room}/notes/{note}', [ReadingController::class, 'updateNote'])->name('room.notes.update');
    Route::delete('/rooms/{room}/notes/{note}', [ReadingController::class, 'destroyNote'])->name('room.notes.destroy');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::get('/notifications/{id}', [NotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::delete('/notifications', [NotificationController::class, 'clear'])->name('notifications.clear');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
});

// админ-панель, пускает только администратора
Route::middleware(['auth', AdminOnly::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/books', [AdminBookController::class, 'index'])->name('books');
    Route::get('/books/create', [AdminBookController::class, 'create'])->name('books.create');
    Route::post('/books/preview', [AdminBookController::class, 'preview'])->name('books.preview');
    Route::post('/books', [AdminBookController::class, 'store'])->name('books.store');
    Route::get('/books/{book}/edit', [AdminBookController::class, 'edit'])->name('books.edit');
    Route::put('/books/{book}', [AdminBookController::class, 'update'])->name('books.update');
    Route::delete('/books/{book}', [AdminBookController::class, 'destroy'])->name('books.destroy');

    // главы книги: список, правка, добавление, удаление
    Route::scopeBindings()->group(function () {
        Route::get('/books/{book}/chapters', [ChapterController::class, 'index'])->name('chapters');
        Route::get('/books/{book}/chapters/create', [ChapterController::class, 'create'])->name('chapters.create');
        Route::post('/books/{book}/chapters', [ChapterController::class, 'store'])->name('chapters.store');
        Route::get('/books/{book}/chapters/{chapter}/edit', [ChapterController::class, 'edit'])->name('chapters.edit');
        Route::put('/books/{book}/chapters/{chapter}', [ChapterController::class, 'update'])->name('chapters.update');
        Route::delete('/books/{book}/chapters/{chapter}', [ChapterController::class, 'destroy'])->name('chapters.destroy');
    });

    // заявки от читателей
    Route::get('/requests', [AdminBookRequestController::class, 'index'])->name('requests');
    Route::put('/requests/{bookRequest}', [AdminBookRequestController::class, 'update'])->name('requests.update');
    Route::delete('/requests/{bookRequest}', [AdminBookRequestController::class, 'destroy'])->name('requests.destroy');

    Route::get('/users', [ManageController::class, 'users'])->name('users');
    Route::delete('/users/{user}', [ManageController::class, 'destroyUser'])->name('users.destroy');
    Route::post('/users/{user}/admin', [ManageController::class, 'makeAdmin'])->name('users.admin');
    Route::delete('/users/{user}/admin', [ManageController::class, 'removeAdmin'])->name('users.admin.remove');
    Route::get('/rooms', [ManageController::class, 'rooms'])->name('rooms');
    Route::get('/log', [ManageController::class, 'log'])->name('log');
    Route::get('/announcements', [ManageController::class, 'announcementForm'])->name('announcements');
    Route::post('/announcements', [ManageController::class, 'announce'])->name('announcements.send');
    Route::delete('/rooms/{room}', [ManageController::class, 'destroyRoom'])->name('rooms.destroy');
});
