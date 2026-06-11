<?php

declare(strict_types=1);

namespace Sunaoka\PHPUnit\Laravel\Localization\Services;

use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use Sunaoka\PHPUnit\Laravel\Localization\NodeVisitor\TranslationKeyVisitor;

class Translation
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var string[]|null
     */
    private $functions = null;

    /**
     * @var string[]|null
     */
    private $methods = null;

    /**
     * @param  string[]|null  $functions
     * @param  string[]|null  $methods
     */
    public function __construct(
        ?array $functions = null,
        ?array $methods = null,
        ?Parser $parser = null
    ) {
        $this->methods = $methods;
        $this->functions = $functions;
        $this->parser = $parser ?? (new ParserFactory)->createForHostVersion();
    }

    /**
     * @return string[]
     */
    public function getKeys(string $code): array
    {
        $ast = $this->parser->parse($code);

        $visitor = new TranslationKeyVisitor($this->functions, $this->methods);

        $traverser = new NodeTraverser;
        $traverser->addVisitor($visitor);
        $traverser->traverse($ast);  // @phpstan-ignore argument.type

        return $visitor->getKeys();
    }
}
