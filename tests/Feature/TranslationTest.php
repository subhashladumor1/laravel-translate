<?php

namespace Subhashladumor1\Translate\Tests\Feature;

use Subhashladumor1\Translate\Tests\TestCase;
use Subhashladumor1\Translate\Facades\Translate;

class TranslationTest extends TestCase
{
    /** @test */
    public function it_can_translate_text()
    {
        $this->markTestSkipped('Requires active API connection');
        
        $translation = Translate::translate('Hello', 'es');
        
        $this->assertIsString($translation);
        $this->assertNotEmpty($translation);
    }

    /** @test */
    public function it_can_detect_language()
    {
        $this->markTestSkipped('Requires active API connection');
        
        $language = Translate::detectLanguage('Hello World');
        
        $this->assertIsString($language);
    }

    /** @test */
    public function it_can_translate_batch()
    {
        $this->markTestSkipped('Requires active API connection');
        
        $texts = ['Hello', 'World', 'Test'];
        $translations = Translate::translateBatch($texts, 'fr');
        
        $this->assertIsArray($translations);
        $this->assertCount(3, $translations);
    }

    /** @test */
    public function it_uses_cache_for_repeated_translations()
    {
        $this->markTestSkipped('Requires active API connection');
        
        // First call
        $first = Translate::translate('Test', 'es');
        
        // Second call should use cache
        $second = Translate::translate('Test', 'es');
        
        $this->assertEquals($first, $second);
    }

    /** @test */
    public function helper_functions_work()
    {
        $this->assertTrue(function_exists('t'));
        $this->assertTrue(function_exists('translate'));
        $this->assertTrue(function_exists('translate_batch'));
        $this->assertTrue(function_exists('detect_language'));
    }
}
