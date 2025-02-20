<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FlashcardController extends Controller
{

    public function index()
    {
        return view('flashcard.index');
    }

    public function game()
    {
        return view('flashcard.game');
    }
}
