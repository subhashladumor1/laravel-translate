<?php

namespace Subhashladumor1\Translate\Commands;

use Illuminate\Console\Command;
use Subhashladumor1\Translate\Facades\Translate;

class TestServicesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:test
                            {--text=Hello World : Text to translate}
                            {--target=es : Target language}
                            {--service= : Specific service to test (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test all translation services to verify they are working';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $text = $this->option('text');
        $target = $this->option('target');
        $specificService = $this->option('service');

        $this->info("Testing Translation Services");
        $this->info("Text: \"{$text}\"");
        $this->info("Target Language: {$target}");
        $this->newLine();

        $services = ['libre', 'lingva', 'mymemory', 'google', 'argos'];
        
        if ($specificService) {
            $services = [$specificService];
        }

        $results = [];
        $successCount = 0;
        $failCount = 0;

        foreach ($services as $serviceName) {
            $this->line("Testing {$serviceName}...");
            
            try {
                $service = app('translate.manager')->service($serviceName);
                
                if (!$service) {
                    $this->error("  ✗ Service not found");
                    $failCount++;
                    continue;
                }

                if (!$service->isEnabled()) {
                    $this->warn("  ⚠ Service is disabled (enable in config)");
                    continue;
                }

                $startTime = microtime(true);
                $translation = $service->translate($text, $target);
                $duration = round((microtime(true) - $startTime) * 1000);

                if (empty($translation) || $translation === $text) {
                    $this->error("  ✗ Translation failed or returned original text");
                    $this->line("    Result: \"{$translation}\"");
                    $failCount++;
                } else {
                    $this->info("  ✓ Success ({$duration}ms)");
                    $this->line("    Translation: \"{$translation}\"");
                    $successCount++;
                }

                $results[$serviceName] = [
                    'success' => !empty($translation) && $translation !== $text,
                    'translation' => $translation,
                    'duration' => $duration,
                ];

            } catch (\Exception $e) {
                $this->error("  ✗ Error: " . $e->getMessage());
                $failCount++;
                $results[$serviceName] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }

            $this->newLine();
        }

        // Summary
        $this->info("═══════════════════════════════════");
        $this->info("Test Summary");
        $this->info("═══════════════════════════════════");
        $this->info("✓ Successful: {$successCount}");
        $this->error("✗ Failed: {$failCount}");
        
        if ($successCount > 0) {
            $this->newLine();
            $this->info("Working Services:");
            foreach ($results as $name => $result) {
                if ($result['success'] ?? false) {
                    $duration = $result['duration'] ?? 'N/A';
                    $this->line("  • {$name} ({$duration}ms)");
                }
            }
        }

        if ($failCount > 0) {
            $this->newLine();
            $this->warn("Failed Services:");
            foreach ($results as $name => $result) {
                if (!($result['success'] ?? false)) {
                    $error = $result['error'] ?? 'Unknown error';
                    $this->line("  • {$name}: {$error}");
                }
            }

            $this->newLine();
            $this->info("Troubleshooting Tips:");
            $this->line("  1. Check internet connection");
            $this->line("  2. Verify service endpoints are accessible");
            $this->line("  3. Check firewall/proxy settings");
            $this->line("  4. Try enabling services in .env:");
            $this->line("     TRANSLATE_GOOGLE_ENABLED=true");
            $this->line("     TRANSLATE_LIBRE_ENABLED=true");
            $this->line("  5. Clear cache: php artisan cache:clear");
        }

        return $successCount > 0 ? Command::SUCCESS : Command::FAILURE;
    }
}
