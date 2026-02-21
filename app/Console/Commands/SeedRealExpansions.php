<?php
namespace App\Console\Commands;

use App\Jobs\ScrapeExpansionJob;
use App\Models\Expansion;
use Illuminate\Console\Command;

class SeedRealExpansions extends Command {
    protected $signature = 'scrape:real-data';
    protected $description = 'Dispatch scraping jobs';

    public function handle(): void {
        $expansions = [
            'Kobaran Biru' => 'https://asia.pokemon-card.com/id/card-search/list/?expansionCodes=MA2&advanced=Submit'
        ];

        foreach ($expansions as $name => $url) {
            $expansion = Expansion::firstOrCreate(['name' => $name], ['total_cards' => 0]);
            ScrapeExpansionJob::dispatch($expansion->id, $url);
        }
    }
}