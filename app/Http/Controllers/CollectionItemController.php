<?php

namespace App\Http\Controllers;

use App\Models\CollectionItem;
use App\Models\Collection;
use Illuminate\Http\Request;

class CollectionItemController extends Controller
{
    public function getItems(Request $request)
    {
        $token = $request->header('X-Collection-Token');
        $collection = Collection::where('private_token', $token)->first();
        if (!$collection) return response()->json([], 404);
        
        return response()->json(CollectionItem::where('collection_id', $collection->id)->with(['card.expansion', 'card.price'])->get());
    }

    public function updateItem(Request $request)
    {
        $token = $request->header('X-Collection-Token');
        $collection = Collection::where('private_token', $token)->first();
        if (!$collection) return response()->json(['error' => 'Unauthorized'], 401);

        $validated = $request->validate([
            'card_id' => 'required|exists:cards,id',
            'is_owned' => 'required|boolean',
            'is_wishlist' => 'required|boolean',
            'quantity' => 'required|integer|min:0'
        ]);

        $item = CollectionItem::updateOrCreate(
            ['collection_id' => $collection->id, 'card_id' => $validated['card_id']],
            ['is_owned' => $validated['is_owned'], 'is_wishlist' => $validated['is_wishlist'], 'quantity' => $validated['quantity']]
        );

        return response()->json($item->load(['card.expansion', 'card.price']));
    }

    public function getSummary(Request $request)
    {
        $token = $request->header('X-Collection-Token');
        $collection = Collection::where('private_token', $token)->first();
        if (!$collection) return response()->json(['total_value' => 0, 'total_cards' => 0]);

        $items = CollectionItem::where('collection_id', $collection->id)
            ->where('is_owned', true)
            ->where('quantity', '>', 0)
            ->with('card.price')
            ->get();

        $totalValue = $items->sum(function ($item) {
            return $item->quantity * ($item->card->price->price ?? 0);
        });

        return response()->json([
            'total_value' => $totalValue,
            'total_cards' => $items->sum('quantity')
        ]);
    }
}