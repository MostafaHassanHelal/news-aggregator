<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\FetchArticlesJob;
use App\Services\NewsAggregatorService;
use Illuminate\Console\Command;

/**
 * Artisan command to fetch articles from news providers.
 * 
 * Usage:
 *   php artisan news:fetch              # Fetch from all providers
 *   php artisan news:fetch --provider=newsapi  # Fetch from specific provider
 *   php artisan news:fetch --sync       # Run synchronously (not queued)
 */
class FetchArticlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:fetch 
                            {--provider= : Specific provider to fetch from (newsapi, guardian, nytimes)}
                            {--sync : Run synchronously instead of queuing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch articles from news providers';

    /**
     * Execute the console command.
     *
     * @param NewsAggregatorService $aggregatorService
     * @return int
     */
    public function handle(NewsAggregatorService $aggregatorService): int
    {
        $provider = $this->option('provider');
        $sync = $this->option('sync');

        $this->info('Starting article fetch...');
        $this->info('Provider: ' . ($provider ?: 'all'));
        $this->info('Mode: ' . ($sync ? 'synchronous' : 'queued'));

        if ($sync) {
            return $this->runSync($aggregatorService, $provider);
        }

        return $this->dispatchJob($provider);
    }

    /**
     * Run the fetch synchronously.
     *
     * @param NewsAggregatorService $aggregatorService
     * @param string|null $provider
     * @return int
     */
    private function runSync(NewsAggregatorService $aggregatorService, ?string $provider): int
    {
        try {
            if ($provider) {
                $result = $aggregatorService->aggregateByProviderName($provider);
                $this->displayResult($provider, $result);
            } else {
                $results = $aggregatorService->aggregateAll();
                foreach ($results as $providerName => $result) {
                    $this->displayResult($providerName, $result);
                }
            }

            $this->info('Article fetch completed successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Fetch failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Dispatch the fetch job to the queue.
     *
     * @param string|null $provider
     * @return int
     */
    private function dispatchJob(?string $provider): int
    {
        FetchArticlesJob::dispatch($provider);
        $this->info('Job dispatched to queue.');
        return Command::SUCCESS;
    }

    /**
     * Display result for a provider.
     *
     * @param string $provider
     * @param array $result
     * @return void
     */
    private function displayResult(string $provider, array $result): void
    {
        if ($result['success'] ?? false) {
            $this->info(sprintf(
                '[%s] Fetched: %d, Stored: %d',
                $provider,
                $result['fetched'] ?? 0,
                $result['stored'] ?? 0
            ));
        } else {
            $this->error(sprintf(
                '[%s] Failed: %s',
                $provider,
                $result['error'] ?? 'Unknown error'
            ));
        }
    }
}
