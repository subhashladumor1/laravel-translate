<?php

namespace Subhashladumor1\Translate\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Subhashladumor1\Translate\Facades\Translate;

class TranslateSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:sync 
                            {--source=en : Source language}
                            {--target= : Target language(s), comma-separated}
                            {--path=lang : Language files directory}
                            {--force : Overwrite existing translations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync translations from source to target language(s)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $sourceLang = $this->option('source');
        $targetLangs = $this->option('target') ? explode(',', $this->option('target')) : [];
        $langPath = resource_path($this->option('path'));
        $force = $this->option('force');

        if (empty($targetLangs)) {
            $this->error('Please specify target language(s) using --target option');
            return Command::FAILURE;
        }

        $sourcePath = $langPath . '/' . $sourceLang;

        if (!is_dir($sourcePath)) {
            $this->error("Source language directory not found: {$sourcePath}");
            return Command::FAILURE;
        }

        $this->info("Syncing translations from '{$sourceLang}' to: " . implode(', ', $targetLangs));
        $this->newLine();

        foreach ($targetLangs as $targetLang) {
            $targetLang = trim($targetLang);
            $this->info("Processing: {$targetLang}");

            $targetPath = $langPath . '/' . $targetLang;
            
            if (!is_dir($targetPath)) {
                File::makeDirectory($targetPath, 0755, true);
            }

            $files = File::files($sourcePath);
            $bar = $this->output->createProgressBar(count($files));
            $bar->start();

            foreach ($files as $file) {
                $filename = $file->getFilename();
                $targetFile = $targetPath . '/' . $filename;

                // Skip if exists and not forcing
                if (file_exists($targetFile) && !$force) {
                    $bar->advance();
                    continue;
                }

                try {
                    $sourceContent = include $file->getPathname();
                    $translations = Translate::translateArray($sourceContent, $targetLang, $sourceLang);

                    $content = "<?php\n\nreturn " . var_export($translations, true) . ";\n";
                    file_put_contents($targetFile, $content);

                    $bar->advance();
                } catch (\Exception $e) {
                    $this->newLine();
                    $this->error("  Failed to translate {$filename}: " . $e->getMessage());
                    $bar->advance();
                }
            }

            $bar->finish();
            $this->newLine();
            $this->info("  ✓ Completed: {$targetLang}");
            $this->newLine();
        }

        $this->info("All translations synced successfully!");
        return Command::SUCCESS;
    }
}
