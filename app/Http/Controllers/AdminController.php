<?php
namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller {
    public function index(Request $request) {
        $cards = Card::with(['expansion'])->orderBy('expansion_id')->orderBy('card_number')->get();
        return Inertia::render('Admin', ['cards' => $cards, 'adminKey' => $request->query('key')]);
    }

    public function update(Request $request, $id) {
        $validated = $request->validate([
            'image_url' => 'nullable|string',
            'rarity' => 'nullable|string'
        ]);
        
        $card = Card::findOrFail($id);
        $card->update(array_filter($validated));

        return redirect()->back();
    }
}