<?php

namespace App\Http\Controllers;

use App\Models\FcGroupCard;
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

    public function result()
    {
        $results = session('fc_group_card_id', null);

        if (!$results) {
            return redirect()->route('flashcard.index');
        }
        $results = FcGroupCard::query()->where('id', $results)->first();
        return view('flashcard.game_result', compact('results'));
    }
}
