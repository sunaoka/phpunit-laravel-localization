<?php

declare(strict_types=1);

namespace Sunaoka\PHPUnit\Laravel\Localization\Concerns;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Blade;
use Symfony\Component\Finder\Finder;

trait UsedTranslationKeys
{
    use TranslationInstance;

    /**
     * Get the path of the directory where there might be files using translation keys.
     *
     * @return string[]
     */
    protected function searchPaths(): array
    {
        return [
            app_path(),
            resource_path('views'),
            base_path('lang'),
        ];
    }

    /**
     * Get the path of views.
     */
    protected function viewPath(): string
    {
        return resource_path('views');
    }

    /**
     * Get the exclude the translation keys.
     *
     * @return string[]
     */
    protected function excludeUsedKeys(): array
    {
        return [];
    }

    /**
     * Get the translation keys used in the program
     *
     * @param  string[]  $excludeKeys
     * @return string[]
     *
     * @throws FileNotFoundException
     */
    protected function getUsedKeys(array $excludeKeys = []): array
    {
        $result = [
            ...$this->getUsedKeysFromSearchPaths(),
            ...$this->getUsedKeysFromViewPath(),
        ];

        return collect($result)
            ->unique()
            ->reject(fn (string $value) => in_array($value, $excludeKeys, true))
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Get the translation key specified in the searchPaths() files
     *
     * @return string[]
     */
    private function getUsedKeysFromSearchPaths(): array
    {
        $keys = [];

        $translationKey = $this->getTranslationInstance();

        $files = Finder::create()->files()->in($this->searchPaths());
        foreach ($files as $file) {
            $code = $file->getContents();
            $key = $translationKey->getKeys($code);
            if (count($key) === 0) {
                continue;
            }

            $keys[] = $key;
        }

        return array_merge([], ...$keys);
    }

    /**
     * Get the translation key specified in the viewPath() files
     *
     * @return string[]
     *
     * @throws FileNotFoundException
     */
    private function getUsedKeysFromViewPath(): array
    {
        $keys = [];

        $translationKey = $this->getTranslationInstance();

        $files = Finder::create()->files()->in($this->viewPath());
        foreach ($files as $file) {
            $code = $this->compileBladeTemplate($file->getPathname());
            $key = $translationKey->getKeys($code);
            if (count($key) === 0) {
                continue;
            }

            $keys[] = $key;
        }

        return array_merge([], ...$keys);
    }

    /**
     * @throws FileNotFoundException
     */
    protected function compileBladeTemplate(string $path): string
    {
        $string = file_get_contents($path);
        if ($string === false) {
            throw new FileNotFoundException("File does not exist at path {$path}.");
        }

        return Blade::compileString($string);
    }
}
