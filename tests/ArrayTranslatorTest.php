<?php

declare(strict_types=1);

namespace MiGears\I18n\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use MiGears\I18n\ArrayTranslator;
use InvalidArgumentException;

#[CoversClass(ArrayTranslator::class)]
final class ArrayTranslatorTest extends TestCase
{
    public function testTranslateExistingKeyReturnsTranslation(): void
    {
        $translator = new ArrayTranslator(['HELLO' => 'Hello']);
        self::assertSame('Hello', $translator->translate('HELLO'));
    }

    public function testTranslateMissingKeyReturnsKeyAsFallback(): void
    {
        $translator = new ArrayTranslator([]);
        self::assertSame('MISSING_KEY', $translator->translate('MISSING_KEY'));
    }

    public function testTranslateWithSingleParamInterpolation(): void
    {
        $translator = new ArrayTranslator(['HELLO_USER' => 'Hello, %user%']);
        self::assertSame('Hello, Alice', $translator->translate('HELLO_USER', ['user' => 'Alice']));
    }

    public function testTranslateWithMultipleParams(): void
    {
        $translator = new ArrayTranslator([
            'WELCOME' => 'Welcome, %user%! You have %count% messages.',
        ]);
        $result = $translator->translate('WELCOME', ['user' => 'Bob', 'count' => 5]);
        self::assertSame('Welcome, Bob! You have 5 messages.', $result);
    }

    public function testTranslateWithNumericParamValue(): void
    {
        $translator = new ArrayTranslator(['COUNT' => 'Total: %count%']);
        self::assertSame('Total: 42', $translator->translate('COUNT', ['count' => 42]));
    }

    public function testTranslateWithEmptyParamsReturnsTranslationAsIs(): void
    {
        $translator = new ArrayTranslator(['HELLO' => 'Hello']);
        self::assertSame('Hello', $translator->translate('HELLO', []));
    }

    public function testTranslateUnusedParamsAreIgnored(): void
    {
        $translator = new ArrayTranslator(['HELLO' => 'Hello']);
        self::assertSame('Hello', $translator->translate('HELLO', ['unused' => 'value']));
    }

    public function testSingleDomainFlatArrayStructure(): void
    {
        $translator = new ArrayTranslator(['HELLO' => 'Hello']);
        self::assertSame('Hello', $translator->translate('HELLO'));
    }

    public function testMultiDomainNestedStructure(): void
    {
        $translator = new ArrayTranslator([
            'messages' => ['HELLO' => 'Hello'],
            'errors' => ['NOT_FOUND' => 'Not found'],
        ]);

        self::assertSame('Hello', $translator->translate('HELLO', [], 'messages'));
        self::assertSame('Not found', $translator->translate('NOT_FOUND', [], 'errors'));
    }

    public function testDefaultDomainIsUsedWhenDomainIsNull(): void
    {
        $translator = new ArrayTranslator([
            'messages' => ['HELLO' => 'Hello from messages'],
            'errors' => ['HELLO' => 'Hello from errors'],
        ], 'messages');

        self::assertSame('Hello from messages', $translator->translate('HELLO'));
    }

    public function testCustomDefaultDomain(): void
    {
        $translator = new ArrayTranslator([
            'app' => ['HELLO' => 'Hello from app'],
        ], 'app');

        self::assertSame('Hello from app', $translator->translate('HELLO'));
    }

    public function testMissingDomainReturnsKeyAsFallback(): void
    {
        $translator = new ArrayTranslator(['messages' => ['HELLO' => 'Hello']]);
        self::assertSame('HELLO', $translator->translate('HELLO', [], 'nonexistent'));
    }

    public function testFromFileWithSingleDomain(): void
    {
        $translator = ArrayTranslator::fromFile(__DIR__ . '/fixtures/single-domain.php');
        self::assertSame('Hello', $translator->translate('HELLO'));
        self::assertSame('Hello, Alice', $translator->translate('HELLO_USER', ['user' => 'Alice']));
    }

    public function testFromFileWithMultiDomain(): void
    {
        $translator = ArrayTranslator::fromFile(__DIR__ . '/fixtures/multi-domain.php');
        self::assertSame('Hello', $translator->translate('HELLO', [], 'messages'));
        self::assertSame('Page not found', $translator->translate('NOT_FOUND', [], 'errors'));
        self::assertSame(
            'Access denied for user admin',
            $translator->translate('FORBIDDEN', ['user' => 'admin'], 'errors')
        );
    }

    public function testFromFileWithCustomDefaultDomain(): void
    {
        $translator = ArrayTranslator::fromFile(
            __DIR__ . '/fixtures/multi-domain.php',
            'validation'
        );
        self::assertSame(
            'The email field is required',
            $translator->translate('REQUIRED', ['field' => 'email'])
        );
    }

    public function testFromFileThrowsExceptionForMissingFile(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ArrayTranslator::fromFile(__DIR__ . '/fixtures/nonexistent.php');
    }

    public function testFromFileThrowsExceptionForInvalidReturn(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ArrayTranslator::fromFile(__DIR__ . '/fixtures/invalid.php');
    }

    public function testInterpolateHandlesSpecialCharacters(): void
    {
        $translator = new ArrayTranslator(['MSG' => 'Hello, %name%!']);
        self::assertSame(
            'Hello, <script>alert(1)</script>!',
            $translator->translate('MSG', ['name' => '<script>alert(1)</script>'])
        );
    }

    public function testMixedStructureThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ArrayTranslator([
            'messages' => ['HELLO' => 'Hello'],
            'broken' => 'not an array',
        ]);
    }

    public function testEmptyTranslationsTreatedAsFlat(): void
    {
        $translator = new ArrayTranslator([]);

        self::assertSame('HELLO', $translator->translate('HELLO'));
    }
}
