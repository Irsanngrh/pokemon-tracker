<?php
namespace App\Http\Controllers;

use App\Models\Expansion;
use App\Models\Card;
use Illuminate\Http\Request;

class CardController extends Controller {
    public function getExpansions() {
        return response()->json(Expansion::withCount('cards')->get());
    }

    public function getCards(Request $request) {
        $expansionId = $request->query('expansion_id');
        if (!$expansionId || $expansionId === 'all') {
            return response()->json(Card::orderBy('expansion_id', 'desc')->orderBy('card_number', 'asc')->get());
        }
        return response()->json(Card::where('expansion_id', $expansionId)->get());
    }
}