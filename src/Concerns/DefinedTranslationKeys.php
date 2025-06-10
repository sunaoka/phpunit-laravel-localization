<?php

declare(strict_types=1);

namespace Sunaoka\PHPUnit\Laravel\Localization\Concerns;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Arr;
use Symfony\Component\Finder\Finder;

trait DefinedTranslationKeys
{
    protected function locale(): string
    {
        /** @var string */
        return app()->getLocale();  // @phpstan-ignore method.nonObject
    }

    /**
     * Get the path of lang.
     */
    protected function langPath(): string
    {
        return base_path('lang');
    }

    /**
     * Get the exclude "lang" files.
     *
     * @return string[]
     */
    protected function excludeLangFiles(): array
    {
        return [
            'auth.php',
            'pagination.php',
            'passwords.php',
            'validation.php',
        ];
    }

    /**
     * Get the exclude "lang" keys.
     *
     * @return string[]
     */
    protected function excludeLangKeys(): array
    {
        return [];
    }

    /**
     * Get the translation keys defined in "lang" directory
     *
     * @param  string[]  $excludeFiles
     * @param  string[]  $excludeKeys
     * @return string[]
     *
     * @throws FileNotFoundException
     */
    protected function getDefinedKeys(array $excludeFiles = [], array $excludeKeys = []): array
    {
        $result = [];

        $files = $this->getLangFiles($excludeFiles);
        foreach ($files as $file) {
            $basename = $file->getBasename('.php');
            $keys = $this->loadPath($file->getPathname());
            foreach ($keys as $key => $value) {
                $result[] = "{$basename}.{$key}";
            }
        }

        return collect($result)
            ->unique()
            ->reject(fn (string $value) => in_array($value, $excludeKeys, true))
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @return array<string, string>
     *
     * @throws FileNotFoundException
     */
    private function loadPath(string $path): array
    {
        if (! is_file($path)) {
            throw new FileNotFoundException("File does not exist at path {$path}.");
        }

        return Arr::dot(include $path);  // @phpstan-ignore return.type, argument.type
    }

    /**
     * @param  string[]  $excludeFiles
     */
    protected function getLangFiles(array $excludeFiles = []): Finder
    {
        $dir = $this->langPath().'/'.$this->locale();

        return Finder::create()->files()->notName($excludeFiles)->in($dir);
    }

    /**
     * @return string[]
     */
    protected function getLanguages(): array
    {
        $dirs = Finder::create()
            ->directories()
            ->notName($this->locale())
            ->depth(0)
            ->in($this->langPath())
            ->sortByName();

        $languages = [];
        foreach ($dirs as $dir) {
            $languages[] = $dir->getBasename();
        }

        return $languages;
    }
}
