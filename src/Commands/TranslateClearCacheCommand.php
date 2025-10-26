<?php

namespace Subhashladumor1\Translate\Commands;

use Illuminate\Console\Command;
use Subhashladumor1\Translate\Facades\Translate;

class TranslateClearCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:clear-cache 
                            {--analytics : Also clear analytics data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear translation cache';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Clearing translation cache...');

        try {
            Translate::clearCache();
            $this->info('✓ Translation cache cleared successfully!');

            if ($this->option('analytics')) {
                app('translate.manager')->clearAnalytics();
                $this->info('✓ Analytics data cleared successfully!');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to clear cache: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
