<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\NewsAggregatorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job to fetch articles from news providers.
 * 
 * This job can be dispatched to a queue for asynchronous processing.
 * It supports fetching from all providers or a specific provider.
 */
class FetchArticlesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public int $backoff = 60;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public int $maxExceptions = 3;

    /**
     * @var string|null Specific provider to fetch from (null = all providers)
     */
    private ?string $providerName;

    /**
     * @var array Filters to pass to the provider
     */
    private array $filters;

    /**
     * Create a new job instance.
     *
     * @param string|null $providerName
     * @param array $filters
     */
    public function __construct(?string $providerName = null, array $filters = [])
    {
        $this->providerName = $providerName;
        $this->filters = $filters;
    }

    /**
     * Execute the job.
     *
     * @param NewsAggregatorService $aggregatorService
     * @return void
     */
    public function handle(NewsAggregatorService $aggregatorService): void
    {
        Log::info('Starting article fetch job', [
            'provider' => $this->providerName ?? 'all',
            'filters' => $this->filters,
        ]);

        try {
            if ($this->providerName) {
                $result = $aggregatorService->aggregateByProviderName($this->providerName, $this->filters);
                Log::info("Fetch completed for {$this->providerName}", $result);
            } else {
                $results = $aggregatorService->aggregateAll($this->filters);
                Log::info('Fetch completed for all providers', ['results' => $results]);
            }
        } catch (\Exception $e) {
            Log::error('Article fetch job failed', [
                'provider' => $this->providerName ?? 'all',
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Article fetch job failed permanently', [
            'provider' => $this->providerName ?? 'all',
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
