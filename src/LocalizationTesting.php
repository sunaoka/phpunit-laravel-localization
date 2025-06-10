<?php

declare(strict_types=1);

namespace Sunaoka\PHPUnit\Laravel\Localization;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;

trait LocalizationTesting
{
    use Concerns\DefinedTranslationKeys;
    use Concerns\UsedTranslationKeys;

    /**
     * Detect translation keys that are only used in “application” files.
     *
     * @throws FileNotFoundException
     */
    public function test_app_files_only_translation_keys(): void
    {
        $usedKeys = $this->getUsedKeys($this->excludeUsedKeys());
        $definedKeys = $this->getDefinedKeys();

        $diff = array_diff($usedKeys, $definedKeys);
        if (count($diff) === 0) {
            self::assertTrue(true);

            return;
        }

        $message = [];
        $message[] = 'The following translation keys are used only in the “application” file:';
        $message[] = implode(PHP_EOL, $diff);

        $this->incomplete(implode(PHP_EOL.PHP_EOL, $message));
    }

    /**
     * Detect translation keys defined only in “lang” files.
     *
     * @throws FileNotFoundException
     */
    public function test_lang_files_only_translation_keys(): void
    {
        $usedKeys = $this->getUsedKeys();
        $definedKeys = $this->getDefinedKeys($this->excludeLangFiles(), $this->excludeLangKeys());

        $diff = array_diff($definedKeys, $usedKeys);
        if (count($diff) === 0) {
            self::assertTrue(true);

            return;
        }

        $message = [];
        $message[] = 'The following translation keys are defined only in the “lang” file:';
        $message[] = implode(PHP_EOL, $diff);

        $this->incomplete(implode(PHP_EOL.PHP_EOL, $message));
    }

    /**
     * Test to see if the translation keys for each language match, including the order
     *
     * @throws FileNotFoundException
     */
    public function tests_that_match_the_translation_key_for_each_language(): void
    {
        $languages = $this->getLanguages();
        if (count($languages) === 0) {
            self::assertTrue(true);

            return;
        }

        $files = $this->getLangFiles($this->excludeLangFiles());

        foreach ($files as $file) {
            $baseTranslations = $this->loadPath($file->getPathname());
            $basePlaceholders = $this->getPlaceholders($baseTranslations);

            foreach ($languages as $language) {
                $path = "{$this->langPath()}/{$language}/{$file->getBasename()}";
                self::assertFileExists($path);

                $translations = $this->loadPath($path);
                self::assertSame(array_keys($baseTranslations), array_keys($translations), "{$language}/{$file->getBasename()} keys do not match.");

                $placeholders = $this->getPlaceholders($translations);
                self::assertSame($basePlaceholders, $placeholders, "{$language}/{$file->getBasename()} placeholders do not match.");
            }
        }
    }

    /**
     * Get placeholders in the translated text
     *
     * @param  array<string, string>  $translations
     * @return array<string, string[]>
     */
    private function getPlaceholders(array $translations): array
    {
        $placeholders = [];
        foreach ($translations as $key => $value) {
            if (preg_match_all('/(:[A-Z0-9]+)/i', $value, $matches) > 0) {
                $placeholders[$key] = (new Collection($matches[1]))->unique()->sort()->values()->all();
            }
        }

        return $placeholders;  // @phpstan-ignore return.type
    }

    protected function incomplete(string $message = ''): void
    {
        self::markTestIncomplete($message);
    }
}
