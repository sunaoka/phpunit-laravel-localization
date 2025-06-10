# Laravel Localization PHPUnit testing

[![Latest](https://poser.pugx.org/sunaoka/phpunit-laravel-localization/v)](https://packagist.org/packages/sunaoka/phpunit-laravel-localization)
[![License](https://poser.pugx.org/sunaoka/phpunit-laravel-localization/license)](https://packagist.org/packages/sunaoka/phpunit-laravel-localization)
[![PHP](https://img.shields.io/packagist/php-v/sunaoka/phpunit-laravel-localization)](composer.json)
[![Test](https://github.com/sunaoka/phpunit-laravel-localization/actions/workflows/test.yml/badge.svg)](https://github.com/sunaoka/phpunit-laravel-localization/actions/workflows/test.yml)
[![codecov](https://codecov.io/gh/sunaoka/phpunit-laravel-localization/graph/badge.svg)](https://codecov.io/gh/sunaoka/phpunit-laravel-localization)

----

## Features

- Detect translation keys that are only used in “application” files.
- Detect translation keys defined only in “lang” files.
- Test to see if the translation keys for each language match, including the order

## Installation

```bash
composer require --dev sunaoka/phpunit-laravel-localization
```

## Usage

```php
<?php
// tests/Unit/LocalizationTest.php

declare(strict_types=1);

namespace Tests\Unit;

use Sunaoka\PHPUnit\Laravel\Localization\LocalizationTesting;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use LocalizationTesting;
}
```
