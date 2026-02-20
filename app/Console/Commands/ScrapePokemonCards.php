<?php

namespace App\Console\Commands;

use App\Jobs\ScrapeExpansionJob;
use App\Models\Expansion;
use Illuminate\Console\Command;

class ScrapePokemonCards extends Command
{
    protected $signature = 'scrape:expansion {name} {url}';
    protected $description = 'Dispatch a job to scrape Pokemon cards from a given URL';

    public function handle(): void
    {
        $name = $this->argument('name');
        $url = $this->argument('url');

        $expansion = Expansion::firstOrCreate(
            ['name' => $name],
            ['total_cards' => 0]
        );

        ScrapeExpansionJob::dispatch($expansion->id, $url);
    }
}