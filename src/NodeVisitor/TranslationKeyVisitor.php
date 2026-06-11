<?php

namespace Sunaoka\PHPUnit\Laravel\Localization\NodeVisitor;

use Illuminate\Support\Facades\Lang;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
class TranslationKeyVisitor extends NodeVisitorAbstract
{
    /**
     * @var string[]
     */
    private $functions;

    /**
     * @var string[]
     */
    private $methods;

    /**
     * @var string[]
     */
    private $keys = [];

    /**
     * @var string[]
     */
    private $transVariables = [];

    /**
     * @var string[]
     */
    private $appTranslatorVariables = [];

    /**
     * @var string[]
     */
    private $langAliases = [];

    /**
     * @var string[]
     */
    private $namespaceAliases = [];

    /**
     * @var string[]
     */
    private $injectedLangVariables = [];

    /**
     * @var string[]
     */
    private $translatorVariables = [];

    /**
     * @param  string[]|null  $functions
     * @param  string[]|null  $methods
     */
    public function __construct(?array $functions = null, ?array $methods = null)
    {
        $this->functions = $functions ?? ['__', 'trans', 'trans_choice'];
        $this->methods = $methods ?? ['get', 'has', 'choice', 'hasForLocale'];
    }

    /**
     * @return string[]
     */
    public function getKeys(): array
    {
        return $this->keys;
    }

    #[\Override]
    public function enterNode(Node $node)
    {
        // Track aliases and namespace aliases from use statements
        if ($node instanceof Node\Stmt\Use_) {
            $this->trackNamespaceAliases($node);
            $this->trackLangAliases($node);
        }

        // $translator = trans();
        if ($node instanceof Node\Expr\Assign && $this->isTransAssignment($node)) {
            $this->recordTransVariable($node->var);
        }

        // $trans = app('translator');
        if ($node instanceof Node\Expr\Assign && $this->isAppTranslatorAssignment($node)) {
            $this->recordAppTranslatorVariable($node->var);
        }

        // Constructor dependency injection (Lang, Translator, facades type hints)
        if ($node instanceof Node\Stmt\ClassMethod && $node->name->toString() === '__construct') {
            $this->detectConstructorInjection($node);
            $this->detectTranslatorInjection($node);
        }

        // __('key'), trans('key'), trans_choice('key')
        if ($node instanceof Node\Expr\FuncCall) {
            $this->detectFunctionCall($node);
        }

        // trans()->get('key'), $translator->get('key'), app('translator')->get('key'), $trans->get('key'), $lang->get('key')
        if ($node instanceof Node\Expr\MethodCall) {
            $this->detectMethodCall($node);
        }

        // Lang::get('key'), Facades\Lang::get('key'), Support\Facades\Lang::get('key'), Foo::get('key'), Bar::get('key'), \Lang::get('key')
        if ($node instanceof Node\Expr\StaticCall) {
            $this->detectStaticCall($node);
        }

        return null;
    }

    /**
     * Track namespace aliases from use statements
     */
    private function trackNamespaceAliases(Node\Stmt\Use_ $use): void
    {
        foreach ($use->uses as $useUse) {
            $alias = $useUse->alias !== null ? $useUse->alias->toString() : $useUse->name->getLast();
            $this->namespaceAliases[$alias] = $useUse->name->toString();
        }
    }

    /**
     * Track Lang facade aliases from use statements
     */
    private function trackLangAliases(Node\Stmt\Use_ $use): void
    {
        foreach ($use->uses as $useUse) {
            if ($useUse->alias === null) {
                continue;
            }

            if ($useUse->name->toString() !== Lang::class) {
                continue;
            }

            $this->langAliases[] = $useUse->alias->toString();
        }
    }

    private function detectFunctionCall(Node\Expr\FuncCall $node): void
    {
        if (! isset($node->args[0])) {
            return;
        }

        if (! $node->args[0] instanceof Node\Arg) {
            return;
        }

        $functionName = $this->getFunctionName($node);
        if (! in_array($functionName, $this->functions, true)) {
            return;
        }

        $this->addKeyFromArgument($node->args[0]);
    }

    private function detectMethodCall(Node\Expr\MethodCall $node): void
    {
        if (! isset($node->args[0])) {
            return;
        }

        if (! $node->args[0] instanceof Node\Arg) {
            return;
        }

        if (! $node->name instanceof Node\Identifier) {
            return;
        }

        if (! in_array($node->name->toString(), $this->methods, true)) {
            return;
        }

        if (! $this->isValidTransSource($node->var)) {
            return;
        }

        $this->addKeyFromArgument($node->args[0]);
    }

    private function detectStaticCall(Node\Expr\StaticCall $node): void
    {
        if (! $node->class instanceof Node\Name) {
            return;
        }

        $className = $this->resolveClassName($node->class);

        // Remove leading backslash and compare
        $normalizedClassName = ltrim($className, '\\');
        $validClasses = array_merge([Lang::class, 'Lang'], $this->langAliases);
        if (! in_array($normalizedClassName, $validClasses, true)) {
            return;
        }

        if (! $node->name instanceof Node\Identifier) {
            return;
        }

        if (! in_array($node->name->toString(), $this->methods, true)) {
            return;
        }

        if (! isset($node->args[0])) {
            return;
        }

        if (! $node->args[0] instanceof Node\Arg) {
            return;
        }

        $this->addKeyFromArgument($node->args[0]);
    }

    /**
     * Resolve class name (handles aliases, namespaces, and fully qualified names)
     */
    private function resolveClassName(Node\Name $name): string
    {
        $parts = $name->getParts();
        $className = $name->toString();
        $normalizedClassName = ltrim($className, '\\');

        // use alias stand-alone
        if (count($parts) === 1 && isset($this->namespaceAliases[$parts[0]])) {
            return $this->namespaceAliases[$parts[0]];
        }

        // Resolving multiple parts (e.g. Facades\Lang, Support\Facades\Lang)
        foreach ($this->namespaceAliases as $alias => $fullNamespace) {
            $normalizedAlias = ltrim($alias, '\\');
            if (! str_starts_with($normalizedClassName, $normalizedAlias)) {
                continue;
            }

            return str_replace($normalizedAlias, $fullNamespace, $normalizedClassName);
        }

        return $normalizedClassName;
    }

    private function isTransAssignment(Node\Expr\Assign $node): bool
    {
        return $node->expr instanceof Node\Expr\FuncCall && $this->getFunctionName($node->expr) === 'trans';
    }

    private function isAppTranslatorAssignment(Node\Expr\Assign $node): bool
    {
        if (! $node->expr instanceof Node\Expr\FuncCall) {
            return false;
        }

        $functionName = $this->getFunctionName($node->expr);
        if ($functionName !== 'app' && $functionName !== 'resolve') {
            return false;
        }

        if (! isset($node->expr->args[0])) {
            return false;
        }

        $arg = $node->expr->args[0];
        if (! $arg instanceof Node\Arg) {
            return false;
        }

        if (! $arg->value instanceof Node\Scalar\String_) {
            return false;
        }

        if ($arg->value->value !== 'translator') {
            return false;
        }

        return true;
    }

    private function recordTransVariable(Node\Expr $var): void
    {
        if ($var instanceof Node\Expr\Variable && is_string($var->name)) {
            $this->transVariables[] = $var->name;
        }
    }

    private function recordAppTranslatorVariable(Node\Expr $var): void
    {
        if ($var instanceof Node\Expr\Variable && is_string($var->name)) {
            $this->appTranslatorVariables[] = $var->name;
        }
    }

    /**
     * Lang facade / Translator instance constructor injection
     */
    private function detectConstructorInjection(Node\Stmt\ClassMethod $node): void
    {
        foreach ($node->params as $param) {
            if (! $param->type instanceof Node\Name) {
                continue;
            }

            if (! $param->var instanceof Node\Expr\Variable) {
                continue;
            }

            if (! is_string($param->var->name)) {
                continue;
            }

            $typeName = ltrim($this->resolveClassName($param->type), '\\');
            if (! in_array($typeName, [Lang::class, 'Lang'], true) && ! in_array($typeName, $this->langAliases, true)) {
                continue;
            }

            $this->injectedLangVariables[] = $param->var->name;
        }
    }

    /**
     * Translator class constructor injection
     */
    private function detectTranslatorInjection(Node\Stmt\ClassMethod $node): void
    {
        foreach ($node->params as $param) {
            if (! $param->type instanceof Node\Name) {
                continue;
            }

            if (! $param->var instanceof Node\Expr\Variable) {
                continue;
            }

            if (! is_string($param->var->name)) {
                continue;
            }

            $resolvedType = ltrim($this->resolveClassName($param->type), '\\');
            if ($resolvedType !== 'Illuminate\Translation\Translator') {
                continue;
            }

            $this->translatorVariables[] = $param->var->name;
        }
    }

    private function isValidTransSource(Node\Expr $expr): bool
    {
        // trans()->get('key')
        if ($expr instanceof Node\Expr\FuncCall && $this->getFunctionName($expr) === 'trans') {
            return true;
        }

        // $translator->get('key')
        if ($expr instanceof Node\Expr\Variable && $this->isTrackedTransVariable($expr)) {
            return true;
        }

        // app('translator')->get('key')
        if ($this->isTranslatorService($expr)) {
            return true;
        }

        // $trans->get('key') with $trans = app('translator')
        if ($expr instanceof Node\Expr\Variable && $this->isAppTranslatorVariable($expr)) {
            return true;
        }

        // $lang->get('key') with $lang = new Lang, $lang = new Translator
        if ($expr instanceof Node\Expr\Variable && $this->isInjectedLangInstance($expr)) {
            return true;
        }

        if ($expr instanceof Node\Expr\Variable && $this->isInjectedTranslator($expr)) {
            return true;
        }

        return false;
    }

    private function isTrackedTransVariable(Node\Expr\Variable $var): bool
    {
        return is_string($var->name) && in_array($var->name, $this->transVariables, true);
    }

    private function isAppTranslatorVariable(Node\Expr\Variable $var): bool
    {
        return is_string($var->name) && in_array($var->name, $this->appTranslatorVariables, true);
    }

    private function isInjectedLangInstance(Node\Expr\Variable $var): bool
    {
        return is_string($var->name) && in_array($var->name, $this->injectedLangVariables, true);
    }

    private function isInjectedTranslator(Node\Expr\Variable $var): bool
    {
        return is_string($var->name) && in_array($var->name, $this->translatorVariables, true);
    }

    private function isTranslatorService(Node\Expr $expr): bool
    {
        if (! $expr instanceof Node\Expr\FuncCall) {
            return false;
        }

        $functionName = $this->getFunctionName($expr);
        if ($functionName !== 'app' && $functionName !== 'resolve') {
            return false;
        }

        if (! isset($expr->args[0])) {
            return false;
        }

        $arg = $expr->args[0];
        if (! $arg instanceof Node\Arg) {
            return false;
        }

        if (! $arg->value instanceof Node\Scalar\String_) {
            return false;
        }

        if ($arg->value->value !== 'translator') {
            return false;
        }

        return true;
    }

    private function addKeyFromArgument(Node\Arg $arg): void
    {
        if (! $arg->value instanceof Node\Scalar\String_) {
            return;
        }

        $key = $arg->value->value;
        if (in_array($key, $this->keys, true)) {
            return;
        }

        $this->keys[] = $key;
    }

    private function getFunctionName(Node\Expr\FuncCall $node): ?string
    {
        if (! $node->name instanceof Node\Name) {
            return null;
        }

        return $node->name->toString();
    }
}
