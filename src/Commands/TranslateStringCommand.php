<?php

namespace Subhashladumor1\Translate\Commands;

use Illuminate\Console\Command;
use Subhashladumor1\Translate\Facades\Translate;

class TranslateStringCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:string 
                            {text : The text to translate}
                            {target : Target language code}
                            {--source=auto : Source language code}
                            {--service= : Specific service to use}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translate a string to target language';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $text = $this->argument('text');
        $target = $this->argument('target');
        $source = $this->option('source') ?? 'auto';

        $this->info("Translating: {$text}");
        $this->info("From: {$source} → To: {$target}");
        
        try {
            $startTime = microtime(true);
            $translation = Translate::translate($text, $target, $source);
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            $this->newLine();
            $this->line("┌─────────────────────────────────────────┐");
            $this->line("│ <fg=green>Translation Result</>                    │");
            $this->line("├─────────────────────────────────────────┤");
            $this->line("│ <fg=cyan>{$translation}</>");
            $this->line("└─────────────────────────────────────────┘");
            $this->newLine();
            $this->info("✓ Translated in {$duration}ms");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Translation failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
