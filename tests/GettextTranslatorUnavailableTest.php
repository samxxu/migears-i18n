<?php

declare(strict_types=1);

namespace MiGears\I18n\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use MiGears\I18n\GettextTranslator;
use RuntimeException;

/**
 * The missing-extension contract.
 *
 * Deliberately kept out of GettextTranslatorTest: that class skips itself in
 * setUp() when the extension is absent, which would leave this exact case
 * unreachable in the very environment it describes. It carries its own group
 * so CI can run it alone in a job where gettext is disabled.
 */
#[CoversClass(GettextTranslator::class)]
#[Group('gettext-unavailable')]
final class GettextTranslatorUnavailableTest extends TestCase
{
    public function testConstructorThrowsWhenGettextNotAvailable(): void
    {
        if (function_exists('gettext')) {
            $this->markTestSkipped('gettext is available, so the missing-extension path cannot be exercised here');
        }

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Gettext extension is not available');

        new GettextTranslator();
    }
}
