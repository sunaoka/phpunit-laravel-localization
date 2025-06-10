<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use PHPUnit\Framework\IncompleteTestError;
use Sunaoka\PHPUnit\Laravel\Localization\LocalizationTesting;

class TestCaseIncompleteTest extends TestCase
{
    use LocalizationTesting;

    /**
     * Get the translation keys used in the program
     *
     * @param  string[]  $excludeKeys
     * @return string[]
     */
    protected function getUsedKeys(array $excludeKeys = []): array
    {
        return ['foo'];
    }

    /**
     * Get the translation keys defined in "lang" directory
     *
     * @param  string[]  $excludeFiles
     * @param  string[]  $excludeKeys
     * @return string[]
     */
    protected function getDefinedKeys(array $excludeFiles = [], array $excludeKeys = []): array
    {
        return ['bar'];
    }

    /**
     * @throws FileNotFoundException
     */
    public function test_app_files_only_translation_keys_override(): void
    {
        // @phpstan-ignore classConstant.internalClass
        $this->expectException(IncompleteTestError::class);

        $this->test_app_files_only_translation_keys();
    }

    /**
     * @throws FileNotFoundException
     */
    public function test_lang_files_only_translation_keys_override(): void
    {
        // @phpstan-ignore classConstant.internalClass
        $this->expectException(IncompleteTestError::class);

        $this->test_lang_files_only_translation_keys();
    }
}
