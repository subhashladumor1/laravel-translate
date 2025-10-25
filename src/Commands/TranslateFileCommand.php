<?php

namespace Subhashladumor1\Translate\Commands;

use Illuminate\Console\Command;
use Subhashladumor1\Translate\Facades\Translate;

class TranslateFileCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:file 
                            {source : Source file path}
                            {target : Target language code}
                            {--output= : Output file path (optional)}
                            {--format=php : Output format (php, json)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translate an entire language file';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $sourcePath = $this->argument('source');
        $targetLang = $this->argument('target');
        $outputPath = $this->option('output');
        $format = $this->option('format');

        if (!file_exists($sourcePath)) {
            $this->error("File not found: {$sourcePath}");
            return Command::FAILURE;
        }

        $this->info("Translating file: {$sourcePath}");
        $this->info("Target language: {$targetLang}");

        try {
            $bar = $this->output->createProgressBar();
            $bar->start();

            $translations = Translate::translateFile($sourcePath, $targetLang);

            $bar->finish();
            $this->newLine(2);

            // Determine output path
            if (!$outputPath) {
                $pathInfo = pathinfo($sourcePath);
                $outputPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . ".{$targetLang}." . $pathInfo['extension'];
            }

            // Save translated content
            if ($format === 'json') {
                file_put_contents($outputPath, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            } else {
                $content = "<?php\n\nreturn " . var_export($translations, true) . ";\n";
                file_put_contents($outputPath, $content);
            }

            $this->info("✓ Translation completed!");
            $this->info("Output saved to: {$outputPath}");
            $this->info("Total items translated: " . count($translations, COUNT_RECURSIVE));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Translation failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
