<?php

namespace App\Jobs;

use App\Models\Card;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Process\Process;

class ScrapeExpansionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $expansionId;
    public $url;
    public $timeout = 7200; 

    public function __construct($expansionId, $url)
    {
        $this->expansionId = $expansionId;
        $this->url = $url;
    }

    public function handle(): void
    {
        $basePath = base_path('python_scraper');
        $pythonExecutable = $basePath . '\venv\Scripts\python.exe';
        $scriptPath = $basePath . '\scraper.py';

        $process = new Process([$pythonExecutable, $scriptPath, $this->url]);
        $process->setTimeout(7200); 
        $process->run();

        if (!$process->isSuccessful()) return;

        $output = $process->getOutput();
        $cardsData = json_decode($output, true);

        if (is_array($cardsData) && count($cardsData) > 0) {
            $parsedCards = [];
            $maxNumerator = 0;
            $commonDenominator = '???';

            foreach ($cardsData as $cardData) {
                $rawNumber = $cardData['card_number'] ?? '';
                if (preg_match('/^0*(\d+)\/(.+)$/', $rawNumber, $matches)) {
                    $numerator = (int)$matches[1];
                    $denominator = $matches[2];
                    $parsedCards[$numerator] = $cardData;
                    if ($numerator > $maxNumerator) $maxNumerator = $numerator;
                    if ($commonDenominator === '???') $commonDenominator = $denominator;
                } else {
                    $parsedCards[$rawNumber] = $cardData;
                }
            }

            for ($i = 1; $i <= $maxNumerator; $i++) {
                if (isset($parsedCards[$i])) {
                    $cardData = $parsedCards[$i];
                } else {
                    $formattedNumerator = str_pad($i, 3, '0', STR_PAD_LEFT);
                    $cardData = [
                        'name' => 'Unknown Card',
                        'card_number' => $formattedNumerator . '/' . $commonDenominator,
                        'image_url' => 'https://via.placeholder.com/240x330.png?text=Missing+Card',
                        'category' => 'Unknown',
                        'illustrator' => '-',
                        'details' => []
                    ];
                }

                Card::updateOrCreate(
                    ['expansion_id' => $this->expansionId, 'card_number' => $cardData['card_number']],
                    [
                        'name' => $cardData['name'],
                        'image_url' => $cardData['image_url'],
                        'category' => $cardData['category'],
                        'illustrator' => $cardData['illustrator'],
                        'details' => $cardData['details']
                    ]
                );
            }

            foreach ($parsedCards as $key => $cardData) {
                if (!is_int($key)) {
                    Card::updateOrCreate(
                        ['expansion_id' => $this->expansionId, 'card_number' => $cardData['card_number'] ?? '000/000'],
                        [
                            'name' => $cardData['name'] ?? 'Unknown',
                            'image_url' => $cardData['image_url'] ?? null,
                            'category' => $cardData['category'] ?? null,
                            'illustrator' => $cardData['illustrator'] ?? null,
                            'details' => $cardData['details'] ?? []
                        ]
                    );
                }
            }
        }
    }
}