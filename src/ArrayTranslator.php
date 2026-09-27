<?php

declare(strict_types=1);

namespace MiGears\I18n;

use InvalidArgumentException;
use Stringable;

/**
 * Array-based translator that loads translations from PHP arrays.
 *
 * Simple usage:
 *   $t = new ArrayTranslator(['HELLO' => 'Hello, %user%']);
 *   echo $t->translate('HELLO', ['user' => 'Alice']); // "Hello, Alice"
 *
 * Multi-domain usage:
 *   $t = new ArrayTranslator([
 *       'messages' => ['HELLO' => 'Hello'],
 *       'errors'   => ['NOT_FOUND' => 'Not found'],
 *   ]);
 */
class ArrayTranslator implements TranslatorInterface
{
    /** @var array<string, array<string, string>> domain => [key => translation] */
    private array $translations;

    private readonly string $defaultDomain;

    /**
     * @param array<string, array<string, string>>|array<string, string> $translations
     *        Flat array (key => translation) for single-domain, or nested array
     *        (domain => [key => translation]) for multi-domain.
     * @param string $defaultDomain default domain name when $domain is null
     */
    public function __construct(array $translations, string $defaultDomain = 'messages')
    {
        $this->defaultDomain = $defaultDomain;
        $this->translations = $this->normalize($translations, $defaultDomain);
    }

    /**
     * Normalize translations into the canonical domain => [key => translation] shape.
     *
     * @param array<string, array<string, string>>|array<string, string> $translations
     * @return array<string, array<string, string>>
     * @throws InvalidArgumentException if the array mixes scalars and arrays at top level,
     *                                  or if any translation value is not a string
     */
    private function normalize(array $translations, string $defaultDomain): array
    {
        if ($translations === []) {
            return [$defaultDomain => []];
        }

        $allArrays = true;
        $allScalars = true;
        foreach ($translations as $value) {
            if (is_array($value)) {
                $allScalars = false;
            } else {
                $allArrays = false;
            }
        }

        if ($allArrays) {
            foreach ($translations as $domain => $entries) {
                self::assertStringValues($entries, (string) $domain);
            }

            return $translations;
        }

        if ($allScalars) {
            self::assertStringValues($translations, $defaultDomain);

            return [$defaultDomain => $translations];
        }

        throw new InvalidArgumentException(
            'Translations must be either flat (key => string) or nested '
            . '(domain => [key => string]), not a mix of both.'
        );
    }

    /**
     * Ensure every translation value is a string.
     *
     * Without this check a stray array or number would surface much later as
     * `TypeError: Return value must be of type string, ... returned` from
     * translate(), which gives the caller no clue which entry is at fault.
     *
     * @param array<array-key, mixed> $entries
     *
     * @throws InvalidArgumentException if a value is not a string
     */
    private static function assertStringValues(array $entries, string $domain): void
    {
        foreach ($entries as $key => $value) {
            if (!is_string($value)) {
                throw new InvalidArgumentException(sprintf(
                    'Translation "%s" in domain "%s" must be a string, %s given',
                    $key,
                    $domain,
                    get_debug_type($value)
                ));
            }
        }
    }

    /**
     * Load translations from a PHP file that returns an array.
     *
     * The file should return either a flat array (single domain) or a
     * nested array keyed by domain name.
     */
    public static function fromFile(string $file, string $defaultDomain = 'messages'): self
    {
        if (!is_file($file)) {
            throw new InvalidArgumentException("Translation file not found: {$file}");
        }

        $translations = require $file;

        if (!is_array($translations)) {
            throw new InvalidArgumentException("Translation file must return an array: {$file}");
        }

        return new self($translations, $defaultDomain);
    }

    /**
     * @param array<string, mixed> $params
     */
    public function translate(string $key, array $params = [], ?string $domain = null): string
    {
        $domain ??= $this->defaultDomain;
        $translation = $this->translations[$domain][$key] ?? $key;

        if ($params === []) {
            return $translation;
        }

        return $this->interpolate($translation, $params);
    }

    /**
     * Interpolate %placeholders% in the translation string.
     *
     * @param array<string, mixed> $params values must be scalar, Stringable or null
     *
     * @throws InvalidArgumentException if a parameter value cannot be rendered as text
     */
    private function interpolate(string $text, array $params): string
    {
        $replace = [];
        foreach ($params as $key => $value) {
            if ($value !== null && !is_scalar($value) && !$value instanceof Stringable) {
                throw new InvalidArgumentException(sprintf(
                    'Interpolation parameter "%s" must be a scalar, Stringable or null, %s given',
                    $key,
                    get_debug_type($value)
                ));
            }

            $replace['%' . $key . '%'] = (string) $value;
        }

        return strtr($text, $replace);
    }
}
