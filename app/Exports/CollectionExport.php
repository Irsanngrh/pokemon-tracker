<?php

namespace App\Exports;

use App\Models\CollectionItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CollectionExport implements FromCollection, WithHeadings, WithMapping
{
    protected $collectionId;

    public function __construct($collectionId)
    {
        $this->collectionId = $collectionId;
    }

    public function collection()
    {
        return CollectionItem::where('collection_id', $this->collectionId)
            ->where('is_owned', true)
            ->with(['card.expansion', 'card.price'])
            ->get();
    }

    public function map($item): array
    {
        $price = $item->card->price->price ?? 0;
        return [
            $item->card->name,
            $item->card->card_number,
            $item->card->expansion->name,
            $item->quantity,
            $price,
            $item->quantity * $price,
        ];
    }

    public function headings(): array
    {
        return ['Card Name', 'Card Number', 'Expansion', 'Quantity', 'Estimated Unit Price (IDR)', 'Subtotal (IDR)'];
    }
}