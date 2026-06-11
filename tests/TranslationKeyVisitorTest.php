<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use Sunaoka\PHPUnit\Laravel\Localization\Services\Translation;

class TranslationKeyVisitorTest extends TestCase
{
    /**
     * @param  string[]  $expected
     *
     * @dataProvider dataProvider
     */
    #[DataProvider('dataProvider')]
    public function test(string $code, array $expected): void
    {
        $actual = (new Translation)->getKeys($code);

        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{string, string[]}>
     */
    public static function dataProvider(): array
    {
        $expected = [
            'get',
            'has',
            'choice',
            'hasForLocale',
        ];

        return [
            'Call helper function' => [
                <<<'PHP'
                    <?php
                    __('__');
                    trans('trans', [], 'en');
                    trans_choice('trans_choice', locale: 'en');
PHP
                ,
                [
                    '__',
                    'trans',
                    'trans_choice',
                ],
            ],

            'Call the method of the trans()' => [
                <<<'PHP'
                    <?php
                    trans()->get('get');
                    trans()->has('has');
                    trans()->choice('choice');
                    trans()->hasForLocale('hasForLocale');
                    trans()->unknownMethod('unknownMethod');
PHP
                ,
                $expected,
            ],

            'Call the method of the trans() (assigned to variable)' => [
                <<<'PHP'
                    <?php
                    $trans = trans();
                    $trans->get('get');
                    $trans->has('has');
                    $trans->choice('choice');
                    $trans->hasForLocale('hasForLocale');
                    $trans->unknownMethod('unknownMethod');
PHP
                ,
                $expected,
            ],

            "Call the method of the app('translator')" => [
                <<<'PHP'
                    <?php
                    app('translator')->get('get');
                    app('translator')->has('has');
                    app('translator')->choice('choice');
                    app('translator')->hasForLocale('hasForLocale');
                    app('translator')->unknownMethod('unknownMethod');
PHP
                ,
                $expected,
            ],

            "Call the method of the app('translator') (assigned to variable)" => [
                <<<'PHP'
                    <?php
                    $translator = app('translator');
                    $translator->get('get');
                    $translator->has('has');
                    $translator->choice('choice');
                    $translator->hasForLocale('hasForLocale');
                    $translator->unknownMethod('unknownMethod');
PHP
                ,
                $expected,
            ],

            "Call the method of the resolve('translator')" => [
                <<<'PHP'
                    <?php
                    resolve('translator')->get('get');
                    resolve('translator')->has('has');
                    resolve('translator')->choice('choice');
                    resolve('translator')->hasForLocale('hasForLocale');
                    resolve('translator')->unknownMethod('unknownMethod');
PHP
                ,
                $expected,
            ],

            "Call the method of the resolve('translator') (assigned to variable)" => [
                <<<'PHP'
                    <?php
                    $translator = resolve('translator');
                    $translator->get('get');
                    $translator->has('has');
                    $translator->choice('choice');
                    $translator->hasForLocale('hasForLocale');
                    $translator->unknownMethod('unknownMethod');
PHP
                ,
                $expected,
            ],

            'Call the \Lang facade static method' => [
                <<<'PHP'
                    <?php
                    \Lang::get('get');
                    \Lang::has('has');
                    \Lang::choice('choice');
                    \Lang::hasForLocale('hasForLocale');
                    \Lang::unknownMethod('unknownMethod');
PHP
                ,
                $expected,
            ],

            'Call the Lang facade static method' => [
                <<<'PHP'
                    <?php
                    use Illuminate\Support\Facades\Lang;
                    Lang::get('get');
                    Lang::has('has');
                    Lang::choice('choice');
                    Lang::hasForLocale('hasForLocale');
                    Lang::unknownMethod('unknownMethod');
PHP
                ,
                $expected,
            ],

            'Call the Lang facade static method 2' => [
                <<<'PHP'
                    <?php
                    use Illuminate\Support\Facades;
                    Facades\Lang::get('get');
                    Facades\Lang::has('has');
                    Facades\Lang::choice('choice');
                    Facades\Lang::hasForLocale('hasForLocale');
                    Facades\Lang::unknownMethod('unknownMethod');
PHP
                ,
                $expected,
            ],

            'Call the Lang facade static method (alias)' => [
                <<<'PHP'
                    <?php
                    use Illuminate\Support\Facades\Lang AS LangAlias;
                    LangAlias::get('get');
                    LangAlias::has('has');
                    LangAlias::choice('choice');
                    LangAlias::hasForLocale('hasForLocale');
                    LangAlias::unknownMethod('unknownMethod');
PHP
                ,
                $expected,
            ],

            'Call the Lang facade static method (fully qualified)' => [
                <<<'PHP'
                    <?php
                    \Illuminate\Support\Facades\Lang::get('get');
                    \Illuminate\Support\Facades\Lang::has('has');
                    \Illuminate\Support\Facades\Lang::choice('choice');
                    \Illuminate\Support\Facades\Lang::hasForLocale('hasForLocale');
                    \Illuminate\Support\Facades\Lang::unknownMethod('unknownMethod');
PHP
                ,
                $expected,
            ],

            'Call the method of the Lang facade with dependency injection' => [
                <<<'PHP'
                    <?php
                    use Illuminate\Support\Facades\Lang;
                    class Foo
                    {
                        public function __construct(Lang $lang)
                        {
                            $lang->get('get');
                            $lang->has('has');
                            $lang->choice('choice');
                            $lang->hasForLocale('hasForLocale');
                            $lang->unknownMethod('unknownMethod');
                        }
                    }
PHP
                ,
                $expected,
            ],

            'Call the method of the Lang facade with dependency injection 2' => [
                <<<'PHP'
                    <?php
                    use Illuminate\Support\Facades;
                    class Foo
                    {
                        public function __construct(Facades\Lang $lang)
                        {
                            $lang->get('get');
                            $lang->has('has');
                            $lang->choice('choice');
                            $lang->hasForLocale('hasForLocale');
                            $lang->unknownMethod('unknownMethod');
                        }
                    }
PHP
                ,
                $expected,
            ],

            'Call the method of the Lang facade with dependency injection (alias)' => [
                <<<'PHP'
                    <?php
                    use Illuminate\Support\Facades\Lang AS LangAlias;
                    class Foo
                    {
                        public function __construct(LangAlias $lang)
                        {
                            $lang->get('get');
                            $lang->has('has');
                            $lang->choice('choice');
                            $lang->hasForLocale('hasForLocale');
                            $lang->unknownMethod('unknownMethod');
                        }
                    }
PHP
                ,
                $expected,
            ],

            'Call the method of the Lang facade with dependency injection (fully qualified)' => [
                <<<'PHP'
                    <?php
                    class Foo
                    {
                        public function __construct(\Illuminate\Support\Facades\Lang $lang)
                        {
                            $lang->get('get');
                            $lang->has('has');
                            $lang->choice('choice');
                            $lang->hasForLocale('hasForLocale');
                            $lang->unknownMethod('unknownMethod');
                        }
                    }
PHP
                ,
                $expected,
            ],

            'Call the method of the Translator with dependency injection' => [
                <<<'PHP'
                    <?php
                    use \Illuminate\Translation\Translator;
                    class Foo
                    {
                        public function __construct(Translator $lang)
                        {
                            $lang->get('get');
                            $lang->has('has');
                            $lang->choice('choice');
                            $lang->hasForLocale('hasForLocale');
                            $lang->unknownMethod('unknownMethod');
                        }
                    }
PHP
                ,
                $expected,
            ],

            'Call the method of the Translator with dependency injection 2' => [
                <<<'PHP'
                    <?php
                    use \Illuminate\Translation;
                    class Foo
                    {
                        public function __construct(Translation\Translator $lang)
                        {
                            $lang->get('get');
                            $lang->has('has');
                            $lang->choice('choice');
                            $lang->hasForLocale('hasForLocale');
                            $lang->unknownMethod('unknownMethod');
                        }
                    }
PHP
                ,
                $expected,
            ],

            'Call the method of the Translator with dependency injection (alias)' => [
                <<<'PHP'
                    <?php
                    use \Illuminate\Translation\Translator AS TranslatorAlias;
                    class Foo
                    {
                        public function __construct(TranslatorAlias $lang)
                        {
                            $lang->get('get');
                            $lang->has('has');
                            $lang->choice('choice');
                            $lang->hasForLocale('hasForLocale');
                            $lang->unknownMethod('unknownMethod');
                        }
                    }
PHP
                ,
                $expected,
            ],

            'Call the method of the Translator with dependency injection (fully qualified)' => [
                <<<'PHP'
                    <?php
                    class Foo
                    {
                        public function __construct(\Illuminate\Translation\Translator $lang)
                        {
                            $lang->get('get');
                            $lang->has('has');
                            $lang->choice('choice');
                            $lang->hasForLocale('hasForLocale');
                            $lang->unknownMethod('unknownMethod');
                        }
                    }
PHP
                ,
                $expected,
            ],

            'Translation key cannot be obtained' => [
                <<<'PHP'
                    <?php
                    use Dummy\Foo\Dummy1;
                    use Dummy\Bar\Dummy2 AS Dummy2Alias;
                    use Dummy\Baz\Dummy3;
                    use Dummy\Qux;
                    dummy('not.translation');
                    dummy()->get('not.translation');
                    $dummy = dummy();
                    $dummy->get('not.translation');
                    app('dummy')->get('not.translation');
                    $dummy = app('dummy');
                    $dummy->get('not.translation');
                    \Dummy::get('not.translation');
                    Dummy1::get('not.translation');
                    Dummy2Alias::get('not.translation');
                    \Dummy\Baz\Dummy3::get('not.translation');
                    Qux\Dummy4::get('not.translation');
                    class Foo
                    {
                        public function __construct(Dummy1 $lang)
                        {
                            $lang->get('get');
                        }
                    }
PHP
                ,
                [],
            ],
        ];
    }
}
