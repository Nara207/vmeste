<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Note;
use App\Models\Room;
use App\Models\User;

// Главная страница: показывает новые книги и общие цифры сайта
class HomeController extends Controller
{
    public function index()
    {
        return view('home', [
            'newBooks' => Book::latest('id')->limit(4)->get(),
            'stats' => [
                'books' => Book::count(),
                'readers' => User::count(),
                'rooms' => Room::count(),
                'notes' => Note::count(),
            ],
        ]);
    }
}
