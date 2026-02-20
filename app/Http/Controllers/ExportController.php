<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\CollectionItem;
use App\Exports\CollectionExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{
    private function getCollection($token)
    {
        return Collection::where('private_token', $token)->first();
    }

    public function exportExcel(Request $request)
    {
        $collection = $this->getCollection($request->query('token'));
        
        if (!$collection) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return Excel::download(new CollectionExport($collection->id), 'Pokemon_Collection.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $collection = $this->getCollection($request->query('token'));
        
        if (!$collection) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $items = CollectionItem::where('collection_id', $collection->id)
            ->where('is_owned', true)
            ->with(['card.expansion', 'card.price'])
            ->get();

        $totalValue = $items->sum(function ($item) {
            return $item->quantity * ($item->card->price->price ?? 0);
        });

        $pdf = Pdf::loadView('exports.collection_pdf', compact('items', 'totalValue'));
        
        return $pdf->download('Pokemon_Collection.pdf');
    }
}