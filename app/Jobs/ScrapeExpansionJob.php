<?php
namespace App\Jobs;

use App\Models\Card;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Process\Process;

class ScrapeExpansionJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $expansionId;
    public $url;
    public $timeout = 7200; 

    public function __construct($expansionId, $url) {
        $this->expansionId = $expansionId;
        $this->url = $url;
    }

    public function handle(): void {
        $basePath = base_path('python_scraper');
        $pythonExecutable = $basePath . '\venv\Scripts\python.exe';
        $scriptPath = $basePath . '\scraper.py';

        $process = new Process([$pythonExecutable, $scriptPath, $this->url]);
        $process->setTimeout(7200); 
        $process->run();

        if (!$process->isSuccessful()) return;

        $output = $process->getOutput();
        $cardsData = json_decode($output, true);

        if (is_array($cardsData)) {
            foreach ($cardsData as $cardData) {
                Card::updateOrCreate(
                    ['expansion_id' => $this->expansionId, 'official_id' => $cardData['official_id']],
                    [
                        'name' => $cardData['name'] ?? 'Unknown',
                        'card_number' => $cardData['card_number'] ?? '--',
                        'image_url' => $cardData['image_url'] ?? null,
                        'category' => $cardData['category'] ?? null,
                        'rarity' => '--',
                        'illustrator' => $cardData['illustrator'] ?? '--',
                        'details' => $cardData['details'] ?? []
                    ]
                );
            }
        }
    }
}