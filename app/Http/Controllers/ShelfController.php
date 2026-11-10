<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Support\Pager;
use Illuminate\Http\Request;

class ShelfController extends Controller
{
    public function index(Request $request)
    {
        $books = auth()->user()->shelf()->withCount('chapters')->orderBy('title')->get();
        [$books, $page, $pages] = Pager::take($books, 11, (int) $request->query('page', 1));
        return view('shelf', compact('books', 'page', 'pages'));
    }

    // Добавить книгу на полку или убрать с неё
    public function toggle(Book $book)
    {
        $changes = auth()->user()->shelf()->toggle($book->id);
        $text = count($changes['attached']) ? 'Книга добавлена на полку' : 'Книга убрана с полки';
        return back()->with('status', $text);
    }
}
