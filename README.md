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
