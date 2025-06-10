<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Sunaoka\PHPUnit\Laravel\Localization\LocalizationTesting;

class TestCaseTest extends TestCase
{
    use LocalizationTesting;

    /**
     * @throws FileNotFoundException
     */
    public function test_used_keys(): void
    {
        $expected = $this->getUsedKeys();

        self::assertSame([
            'messages.nested.key',
            'messages.nested.nested.key',
            'messages.welcome',
        ], $expected);
    }

    /**
     * @throws FileNotFoundException
     */
    public function test_defined_keys(): void
    {
        $expected = $this->getDefinedKeys();

        self::assertSame([
            'messages.nested.key',
            'messages.nested.nested.key',
            'messages.welcome',
        ], $expected);
    }
}
