<?php

namespace Database\Seeders;

use App\Models\Expansion;
use App\Models\Card;
use App\Models\CardPrice;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $expansion = Expansion::firstOrCreate(
            ['name' => 'Promo Test'],
            ['total_cards' => 4]
        );

        $cards = [
            ['name' => 'Pikachu', 'card_number' => '001/PROMO', 'image_url' => 'https://asia.pokemon-card.com/id/card-img/id00017493.png', 'price' => 150000],
            ['name' => 'Charizard', 'card_number' => '002/PROMO', 'image_url' => 'https://asia.pokemon-card.com/id/card-img/id00017493.png', 'price' => 2500000],
            ['name' => 'Bulbasaur', 'card_number' => '003/PROMO', 'image_url' => 'https://asia.pokemon-card.com/id/card-img/id00017493.png', 'price' => 50000],
            ['name' => 'Squirtle', 'card_number' => '004/PROMO', 'image_url' => 'https://asia.pokemon-card.com/id/card-img/id00017493.png', 'price' => 55000],
        ];

        foreach ($cards as $cardData) {
            $card = Card::firstOrCreate(
                ['expansion_id' => $expansion->id, 'card_number' => $cardData['card_number']],
                ['name' => $cardData['name'], 'image_url' => $cardData['image_url'], 'rarity' => 'Promo']
            );

            CardPrice::updateOrCreate(
                ['card_id' => $card->id],
                ['price' => $cardData['price'], 'is_manual_override' => true]
            );
        }
    }
}