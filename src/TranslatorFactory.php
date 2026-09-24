<?php

declare(strict_types=1);

namespace MiGears\I18n;

use InvalidArgumentException;

/**
 * Factory for creating translator instances from configuration arrays.
 *
 * Supports two drivers out of the box:
 *   - array    : PHP array-based translations (ArrayTranslator)
 *   - gettext  : Native gettext extension (GettextTranslator, requires ext-gettext)
 *
 * Usage:
 *   $translator = TranslatorFactory::create([
 *       'driver' => 'array',
 *       'translations' => ['HELLO' => 'Hello, %user%'],
 *   ]);
 *
 *   $translator = TranslatorFactory::create([
 *       'driver'    => 'gettext',
 *       'locale'    => 'zh_CN.UTF-8',
 *       'domain'    => 'messages',
 *       'directory' => __DIR__ . '/locale',
 *   ]);
 */
class TranslatorFactory
{
    /**
     * Create a translator from a configuration array.
     *
     * @param array<string, mixed> $config
     *
     * Common config keys:
     *   - driver: "array" (default) or "gettext"
     *   - defaultDomain: default text domain name
     *
     * Array driver config:
     *   - translations: array of translations (flat or nested by domain)
     *   - file: path to a PHP file that returns the translations array
     *
     * Providing both "file" and "translations" is rejected, since the two
     * sources contradict each other.
     *
     * Gettext driver config:
     *   - locale: locale string (e.g. "zh_CN.UTF-8")
     *   - domain: text domain name (alias: defaultDomain)
     *   - directory: path to locale directory
     *   - codeset: character encoding (default "UTF-8")
     *
     * @throws InvalidArgumentException if driver is unknown or config is invalid
     */
    public static function create(array $config): TranslatorInterface
    {
        $driver = $config['driver'] ?? 'array';

        return match ($driver) {
            'array' => self::createArray($config),
            'gettext' => self::createGettext($config),
            default => throw new InvalidArgumentException("Unknown translator driver: {$driver}"),
        };
    }

    /**
     * Create an ArrayTranslator from config.
     *
     * @param array<string, mixed> $config
     */
    private static function createArray(array $config): ArrayTranslator
    {
        $defaultDomain = $config['defaultDomain'] ?? 'messages';

        if (isset($config['file']) && isset($config['translations'])) {
            throw new InvalidArgumentException(
                'Array driver config: provide either "file" or "translations", not both'
            );
        }

        if (isset($config['file'])) {
            return ArrayTranslator::fromFile((string) $config['file'], $defaultDomain);
        }

        $translations = $config['translations'] ?? [];

        if (!is_array($translations)) {
            throw new InvalidArgumentException('Array driver config: "translations" must be an array');
        }

        return new ArrayTranslator($translations, $defaultDomain);
    }

    /**
     * Create a GettextTranslator from config.
     *
     * @param array<string, mixed> $config
     */
    private static function createGettext(array $config): GettextTranslator
    {
        $domain = $config['domain'] ?? $config['defaultDomain'] ?? 'messages';

        return new GettextTranslator(
            defaultDomain: (string) $domain,
            locale: (string) ($config['locale'] ?? 'en_US.UTF-8'),
            directory: (string) ($config['directory'] ?? './locale'),
            codeset: (string) ($config['codeset'] ?? 'UTF-8'),
        );
    }
}
