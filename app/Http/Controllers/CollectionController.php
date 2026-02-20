<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\CollectionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CollectionController extends Controller
{
    public function init()
    {
        $collection = Collection::create([
            'private_token' => Str::uuid()->toString(),
            'public_slug' => Str::random(8),
            'last_active_at' => now(),
        ]);
        return response()->json(['private_token' => $collection->private_token, 'public_slug' => $collection->public_slug]);
    }

    public function verify(Request $request)
    {
        $token = $request->header('X-Collection-Token');
        $collection = Collection::where('private_token', $token)->first();
        if (!$collection) return response()->json(['valid' => false], 404);
        $collection->update(['last_active_at' => now()]);
        return response()->json(['valid' => true, 'public_slug' => $collection->public_slug]);
    }

    public function showPublic($slug)
    {
        $collection = Collection::where('public_slug', $slug)->firstOrFail();
        $items = CollectionItem::where('collection_id', $collection->id)
            ->where(function($q) { $q->where('is_owned', true)->orWhere('is_wishlist', true); })
            ->with(['card.expansion', 'card.price'])
            ->get();
            
        $totalValue = $items->where('is_owned', true)->sum(function ($item) { return $item->quantity * ($item->card->price->price ?? 0); });
        $totalCards = $items->where('is_owned', true)->sum('quantity');

        return Inertia::render('PublicView', [
            'slug' => $slug,
            'items' => $items,
            'summary' => ['total_value' => $totalValue, 'total_cards' => $totalCards]
        ]);
    }
}