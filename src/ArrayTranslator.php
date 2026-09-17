<?php

declare(strict_types=1);

namespace MiGears\I18n;

use InvalidArgumentException;

/**
 * Array-based translator that loads translations from PHP arrays.
 *
 * Simple usage:
 *   $t = new ArrayTranslator(['HELLO' => 'Hello, %user%'], ['user' => 'world']);
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

        // Detect flat vs nested structure
        $first = reset($translations);
        if (is_array($first)) {
            $this->translations = $translations;
        } else {
            $this->translations = [$defaultDomain => $translations];
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
     * @param array<string, mixed> $params
     */
    private function interpolate(string $text, array $params): string
    {
        $replace = [];
        foreach ($params as $key => $value) {
            $replace['%' . $key . '%'] = (string) $value;
        }

        return strtr($text, $replace);
    }
}
