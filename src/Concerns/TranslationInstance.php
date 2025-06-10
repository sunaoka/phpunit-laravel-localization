<?php

declare(strict_types=1);

namespace Sunaoka\PHPUnit\Laravel\Localization\Concerns;

use Sunaoka\PHPUnit\Laravel\Localization\Services\Translation;

trait TranslationInstance
{
    /**
     * @return string[]|null
     */
    protected function functions(): ?array
    {
        return null;
    }

    /**
     * @return string[]|null
     */
    protected function methods(): ?array
    {
        return null;
    }

    protected function getTranslationInstance(): Translation
    {
        return new Translation($this->functions(), $this->methods());
    }
}
