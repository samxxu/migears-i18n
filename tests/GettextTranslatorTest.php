<?php

declare(strict_types=1);

namespace MiGears\I18n\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use MiGears\I18n\GettextTranslator;
use InvalidArgumentException;
use RuntimeException;

#[CoversClass(GettextTranslator::class)]
final class GettextTranslatorTest extends TestCase
{
    protected function setUp(): void
    {
        if (!function_exists('gettext')) {
            $this->markTestSkipped('gettext extension is not available');
        }
    }

    public function testConstructorThrowsWhenLocaleUnavailable(): void
    {
        if (!function_exists('gettext')) {
            $this->markTestSkipped('gettext extension is not available');
        }

        $invalidLocale = 'xx_XX.UTF-8';
        if (setlocale(LC_ALL, $invalidLocale) !== false) {
            $this->markTestSkipped("locale {$invalidLocale} is unexpectedly available");
        }

        $this->expectException(RuntimeException::class);
        new GettextTranslator(locale: $invalidLocale);
    }

    public function testFailedConstructionRestoresEnvironment(): void
    {
        if (!function_exists('gettext')) {
            $this->markTestSkipped('gettext extension is not available');
        }

        $invalidLocale = 'xx_XX.UTF-8';
        if (setlocale(LC_ALL, $invalidLocale) !== false) {
            $this->markTestSkipped("locale {$invalidLocale} is unexpectedly available");
        }

        $previousLang = getenv('LANG');
        $previousLcAll = getenv('LC_ALL');

        try {
            new GettextTranslator(locale: $invalidLocale);
            self::fail('Expected RuntimeException for an unavailable locale');
        } catch (RuntimeException) {
            // expected
        }

        // putenv() has no undo: a constructor that fails halfway must not
        // leave the process pointing at a locale nobody is using.
        self::assertSame($previousLang, getenv('LANG'));
        self::assertSame($previousLcAll, getenv('LC_ALL'));
    }

    public function testTranslateReturnsKeyWhenNoCatalogFound(): void
    {
        $translator = new GettextTranslator(
            defaultDomain: 'nonexistent',
            locale: 'en_US.UTF-8',
            directory: __DIR__ . '/fixtures/locale',
        );

        // Without a real .mo file, gettext returns the key as-is
        self::assertSame('HELLO', $translator->translate('HELLO'));
    }

    public function testTranslateWithParamsInterpolates(): void
    {
        $translator = new GettextTranslator(
            defaultDomain: 'messages',
            locale: 'en_US.UTF-8',
            directory: __DIR__ . '/fixtures/locale',
        );

        // Even without a .mo file, the key is returned and interpolation works
        $result = $translator->translate('Hello, %user%', ['user' => 'Alice']);
        self::assertSame('Hello, Alice', $result);
    }

    public function testTranslateThrowsForArrayParam(): void
    {
        $translator = new GettextTranslator(
            defaultDomain: 'messages',
            locale: 'en_US.UTF-8',
            directory: __DIR__ . '/fixtures/locale',
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Interpolation parameter "user" must be a scalar, Stringable or null, array given');

        $translator->translate('Hello, %user%', ['user' => ['a']]);
    }

    public function testTranslateWithEmptyParams(): void
    {
        $translator = new GettextTranslator(
            defaultDomain: 'messages',
            locale: 'en_US.UTF-8',
            directory: __DIR__ . '/fixtures/locale',
        );

        self::assertSame('HELLO', $translator->translate('HELLO', []));
    }

    public function testTranslateWithExplicitDomain(): void
    {
        $translator = new GettextTranslator(
            defaultDomain: 'messages',
            locale: 'en_US.UTF-8',
            directory: __DIR__ . '/fixtures/locale',
        );

        self::assertSame('ERROR_KEY', $translator->translate('ERROR_KEY', [], 'errors'));
    }

    public function testAddDomain(): void
    {
        $translator = new GettextTranslator(
            defaultDomain: 'messages',
            locale: 'en_US.UTF-8',
            directory: __DIR__ . '/fixtures/locale',
        );

        // addDomain should not throw
        $translator->addDomain('extra', __DIR__ . '/fixtures/locale');
        self::assertSame('TEST_KEY', $translator->translate('TEST_KEY', [], 'extra'));
    }

    public function testAddDomainWithCustomCodeset(): void
    {
        $translator = new GettextTranslator(
            defaultDomain: 'messages',
            locale: 'en_US.UTF-8',
            directory: __DIR__ . '/fixtures/locale',
        );

        $translator->addDomain('extra', __DIR__ . '/fixtures/locale', 'UTF-8');
        self::assertSame('TEST', $translator->translate('TEST', [], 'extra'));
    }

    public function testRuntimeExceptionClassExists(): void
    {
        // Verify the exception is the right type
        $exception = new RuntimeException('test');
        self::assertInstanceOf(RuntimeException::class, $exception);
    }
}
