<?php

namespace App\Console\Commands;

use App\Jobs\ScrapeExpansionJob;
use App\Models\Expansion;
use Illuminate\Console\Command;

class SeedRealExpansions extends Command
{
    protected $signature = 'scrape:real-data';
    protected $description = 'Dispatch scraping jobs for official expansions with dummy fillers';

    public function handle(): void
    {
        $expansions = [
            'Evolusi Mega Impian EX' => 'https://asia.pokemon-card.com/id/card-search/list/?expansionCodes=MA3&advanced=Submit',
            'Kobaran Biru' => 'https://asia.pokemon-card.com/id/card-search/list/?expansionCodes=MA2&advanced=Submit',
            'Evolusi Mega' => 'https://asia.pokemon-card.com/id/card-search/list/?expansionCodes=MA1&advanced=Submit',
            'Hitam & Putih' => 'https://asia.pokemon-card.com/id/card-search/list/?expansionCodes=SV11s&advanced=Submit',
            'Kehadiran Juara' => 'https://asia.pokemon-card.com/id/card-search/list/?expansionCodes=SV10s&advanced=Submit',
            'Ikatan Takdir' => 'https://asia.pokemon-card.com/id/card-search/list/?expansionCodes=SV9s&advanced=Submit',
            'Festival Terastal EX' => 'https://asia.pokemon-card.com/id/card-search/list/?expansionCodes=SV8a&advanced=Submit',
            'Kilat Rasi' => 'https://asia.pokemon-card.com/id/card-search/list/?expansionCodes=SV8s&advanced=Submit',
            'Bimbingan Rasi' => 'https://asia.pokemon-card.com/id/card-search/list/?expansionCodes=SV7s&advanced=Submit'
        ];

        foreach ($expansions as $name => $url) {
            $expansion = Expansion::firstOrCreate(
                ['name' => $name],
                ['total_cards' => 0]
            );

            ScrapeExpansionJob::dispatch($expansion->id, $url);
        }
    }
}