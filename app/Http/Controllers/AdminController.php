<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\CardPrice;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $cards = Card::with(['expansion', 'price'])->orderBy('expansion_id')->orderBy('card_number')->get();
        
        return Inertia::render('Admin', [
            'cards' => $cards,
            'adminKey' => $request->query('key')
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'price' => 'required|numeric|min:0',
            'image_url' => 'nullable|string'
        ]);

        $card = Card::findOrFail($id);
        
        if ($validated['image_url']) {
            $card->update(['image_url' => $validated['image_url']]);
        }

        CardPrice::updateOrCreate(
            ['card_id' => $card->id],
            ['price' => $validated['price'], 'is_manual_override' => true]
        );

        return redirect()->back();
    }
}