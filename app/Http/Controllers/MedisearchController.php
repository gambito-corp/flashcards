<?php

namespace App\Http\Controllers;

use App\Models\MedisearchChat;
use App\Services\MercadoPagoService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenAI\Laravel\Facades\OpenAI;

class MedisearchController extends Controller
{
    public function index()
    {
        return view('medisearch.index');
    }
    public function conversation(Request $request, $chatId)
    {
        if($request->headers->get('key') === null)
            return response('Unauthorized.', 401);

        $chat = MedisearchChat::query()->with('questions')->where('id', $chatId)->first();
        $historial = collect();
        $chat->questions->each(function ($item) use ($historial){
            $historial->push($item->toConversationEntry());
        });
        return response()->json($historial);
    }
}
