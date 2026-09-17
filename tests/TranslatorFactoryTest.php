<?php

declare(strict_types=1);

namespace MiGears\I18n\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use MiGears\I18n\TranslatorFactory;
use MiGears\I18n\ArrayTranslator;
use MiGears\I18n\GettextTranslator;
use MiGears\I18n\TranslatorInterface;
use InvalidArgumentException;

#[CoversClass(TranslatorFactory::class)]
final class TranslatorFactoryTest extends TestCase
{
    // --- Default driver (array) ---

    public function testCreateWithDefaultsReturnsArrayTranslator(): void
    {
        $translator = TranslatorFactory::create([]);
        self::assertInstanceOf(ArrayTranslator::class, $translator);
        self::assertInstanceOf(TranslatorInterface::class, $translator);
    }

    // --- Array driver ---

    public function testCreateArrayWithTranslations(): void
    {
        $translator = TranslatorFactory::create([
            'driver' => 'array',
            'translations' => [
                'HELLO' => 'Hello',
                'GREETING' => 'Hello, %user%',
            ],
        ]);

        self::assertSame('Hello', $translator->translate('HELLO'));
        self::assertSame('Hello, Alice', $translator->translate('GREETING', ['user' => 'Alice']));
    }

    public function testCreateArrayWithFile(): void
    {
        $translator = TranslatorFactory::create([
            'driver' => 'array',
            'file' => __DIR__ . '/fixtures/single-domain.php',
        ]);

        self::assertSame('Hello', $translator->translate('HELLO'));
    }

    public function testCreateArrayWithCustomDefaultDomain(): void
    {
        $translator = TranslatorFactory::create([
            'driver' => 'array',
            'defaultDomain' => 'errors',
            'translations' => [
                'errors' => [
                    'NOT_FOUND' => 'Page not found',
                ],
            ],
        ]);

        self::assertSame('Page not found', $translator->translate('NOT_FOUND'));
    }

    public function testCreateArrayWithInvalidTranslationsType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"translations" must be an array');

        TranslatorFactory::create([
            'driver' => 'array',
            'translations' => 'not an array',
        ]);
    }

    // --- Gettext driver ---

    public function testCreateGettextReturnsGettextTranslator(): void
    {
        if (!function_exists('gettext')) {
            $this->markTestSkipped('gettext extension is not available');
        }

        $translator = TranslatorFactory::create([
            'driver' => 'gettext',
            'locale' => 'en_US.UTF-8',
            'domain' => 'messages',
            'directory' => __DIR__ . '/fixtures/locale',
        ]);

        self::assertInstanceOf(GettextTranslator::class, $translator);
    }

    public function testCreateGettextWithDefaultDomainAlias(): void
    {
        if (!function_exists('gettext')) {
            $this->markTestSkipped('gettext extension is not available');
        }

        $translator = TranslatorFactory::create([
            'driver' => 'gettext',
            'locale' => 'en_US.UTF-8',
            'defaultDomain' => 'messages',
            'directory' => __DIR__ . '/fixtures/locale',
        ]);

        self::assertInstanceOf(GettextTranslator::class, $translator);
    }

    public function testCreateGettextWithDefaults(): void
    {
        if (!function_exists('gettext')) {
            $this->markTestSkipped('gettext extension is not available');
        }

        // Should not throw with minimal config
        $translator = TranslatorFactory::create([
            'driver' => 'gettext',
        ]);

        self::assertInstanceOf(GettextTranslator::class, $translator);
    }

    // --- Unknown driver ---

    public function testCreateWithUnknownDriverThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown translator driver: redis');

        TranslatorFactory::create([
            'driver' => 'redis',
        ]);
    }

    // --- Version constant ---

    public function testVersionConstant(): void
    {
        self::assertSame('2.0.0', TranslatorFactory::VERSION);
    }
}
