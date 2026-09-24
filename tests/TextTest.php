<?php

declare(strict_types=1);

namespace MiGears\I18n\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use MiGears\I18n\ArrayTranslator;
use MiGears\I18n\Text;
use MiGears\I18n\TranslatorInterface;

#[CoversClass(Text::class)]
final class TextTest extends TestCase
{
    public function testGetKeyReturnsOriginalKey(): void
    {
        $text = new Text('HELLO');
        self::assertSame('HELLO', $text->getKey());
    }

    public function testGetParamsReturnsEmptyArrayByDefault(): void
    {
        $text = new Text('HELLO');
        self::assertSame([], $text->getParams());
    }

    public function testGetParamsReturnsProvidedParams(): void
    {
        $text = new Text('HELLO_USER', ['user' => 'Alice']);
        self::assertSame(['user' => 'Alice'], $text->getParams());
    }

    public function testGetDomainReturnsNullByDefault(): void
    {
        $text = new Text('HELLO');
        self::assertNull($text->getDomain());
    }

    public function testGetDomainReturnsProvidedDomain(): void
    {
        $text = new Text('HELLO', [], 'errors');
        self::assertSame('errors', $text->getDomain());
    }

    public function testToStringWithoutTranslatorReturnsKey(): void
    {
        $text = new Text('HELLO');
        self::assertSame('HELLO', (string) $text);
    }

    public function testToStringWithoutTranslatorInterpolatesParamsIntoKey(): void
    {
        $text = new Text('Hello, %user%', ['user' => 'Alice']);
        self::assertSame('Hello, Alice', (string) $text);
    }

    public function testToStringWithoutTranslatorAndNoParamsReturnsKey(): void
    {
        $text = new Text('HELLO', []);
        self::assertSame('HELLO', (string) $text);
    }

    public function testToStringWithTranslatorCallsTranslate(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects(self::once())
            ->method('translate')
            ->with('HELLO', [], null)
            ->willReturn('Hello');

        $text = new Text('HELLO');
        $text->setTranslator($translator);

        self::assertSame('Hello', (string) $text);
    }

    public function testToStringWithTranslatorPassesParams(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects(self::once())
            ->method('translate')
            ->with('HELLO_USER', ['user' => 'Alice'], null)
            ->willReturn('Hello, Alice');

        $text = new Text('HELLO_USER', ['user' => 'Alice']);
        $text->setTranslator($translator);

        self::assertSame('Hello, Alice', (string) $text);
    }

    public function testToStringWithTranslatorPassesDomain(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects(self::once())
            ->method('translate')
            ->with('NOT_FOUND', [], 'errors')
            ->willReturn('Page not found');

        $text = new Text('NOT_FOUND', [], 'errors');
        $text->setTranslator($translator);

        self::assertSame('Page not found', (string) $text);
    }

    public function testSetTranslatorReturnsSelfForFluentInterface(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $text = new Text('HELLO');
        $result = $text->setTranslator($translator);

        self::assertSame($text, $result);
    }

    public function testJsonSerializeReturnsArrayWithKeyParamsDomain(): void
    {
        $text = new Text('HELLO_USER', ['user' => 'Alice'], 'messages');
        $json = $text->jsonSerialize();

        self::assertSame([
            'key' => 'HELLO_USER',
            'params' => ['user' => 'Alice'],
            'domain' => 'messages',
        ], $json);
    }

    public function testJsonSerializeWithNoParamsAndNoDomain(): void
    {
        $text = new Text('HELLO');
        $json = $text->jsonSerialize();

        self::assertSame([
            'key' => 'HELLO',
            'params' => [],
            'domain' => null,
        ], $json);
    }

    public function testFromJsonCreatesTextFromArray(): void
    {
        $text = Text::fromJson([
            'key' => 'HELLO_USER',
            'params' => ['user' => 'Bob'],
            'domain' => 'messages',
        ]);

        self::assertSame('HELLO_USER', $text->getKey());
        self::assertSame(['user' => 'Bob'], $text->getParams());
        self::assertSame('messages', $text->getDomain());
    }

    public function testFromJsonWithMinimalData(): void
    {
        $text = Text::fromJson(['key' => 'HELLO']);

        self::assertSame('HELLO', $text->getKey());
        self::assertSame([], $text->getParams());
        self::assertNull($text->getDomain());
    }

    public function testFromJsonWithNullDomain(): void
    {
        $text = Text::fromJson(['key' => 'HELLO', 'domain' => null]);

        self::assertNull($text->getDomain());
    }

    public function testFromJsonThrowsWhenKeyMissing(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Text::fromJson(['params' => ['user' => 'Alice']]);
    }

    public function testFromJsonThrowsWhenKeyNotString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Text::fromJson(['key' => 123]);
    }

    public function testRoundTripJsonSerializeAndFromJson(): void
    {
        $original = new Text('WELCOME', ['user' => 'Alice', 'count' => 3], 'app');
        $json = $original->jsonSerialize();
        $restored = Text::fromJson($json);

        self::assertSame($original->getKey(), $restored->getKey());
        self::assertSame($original->getParams(), $restored->getParams());
        self::assertSame($original->getDomain(), $restored->getDomain());
    }

    public function testToStringWithRealArrayTranslator(): void
    {
        $translator = new ArrayTranslator([
            'HELLO_USER' => 'Hello, %user%!',
        ]);

        $text = new Text('HELLO_USER', ['user' => 'World']);
        $text->setTranslator($translator);

        self::assertSame('Hello, World!', (string) $text);
    }

    public function testTextIsStringable(): void
    {
        $text = new Text('HELLO');
        self::assertInstanceOf(\Stringable::class, $text);
    }

    public function testTextIsJsonSerializable(): void
    {
        $text = new Text('HELLO');
        self::assertInstanceOf(\JsonSerializable::class, $text);
    }

    public function testJsonEncodeProducesValidJson(): void
    {
        $text = new Text('HELLO_USER', ['user' => 'Alice']);
        $json = json_encode($text);

        self::assertNotFalse($json);
        $decoded = json_decode($json, true);
        self::assertSame('HELLO_USER', $decoded['key']);
        self::assertSame(['user' => 'Alice'], $decoded['params']);
    }
}
