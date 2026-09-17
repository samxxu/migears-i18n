<?php

declare(strict_types=1);

namespace MiGears\I18n;

use JsonSerializable;
use Stringable;

/**
 * Lazy translation text object.
 *
 * Holds a translation key and its parameters, deferring actual translation
 * until __toString() is called. This allows passing translatable text around
 * without needing the translator at construction time.
 *
 * Usage:
 *   $text = new Text('HELLO_USER', ['user' => 'Alice']);
 *   $text->setTranslator($translator);
 *   echo $text; // "Hello, Alice"
 */
final class Text implements Stringable, JsonSerializable
{
    public const VERSION = '2.0.0';

    private ?TranslatorInterface $translator = null;

    /**
     * @param string               $key    translation key
     * @param array<string, mixed> $params interpolation parameters
     * @param string|null          $domain optional text domain
     */
    public function __construct(
        private readonly string $key,
        private readonly array $params = [],
        private readonly ?string $domain = null,
    ) {
    }

    /**
     * Inject the translator used when __toString() is called.
     */
    public function setTranslator(TranslatorInterface $translator): self
    {
        $this->translator = $translator;

        return $this;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return array<string, mixed>
     */
    public function getParams(): array
    {
        return $this->params;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * Translate and return the string.
     *
     * If no translator is set, returns the key as-is (with params interpolated
     * using %placeholder% syntax) as a graceful fallback.
     */
    public function __toString(): string
    {
        if ($this->translator !== null) {
            return $this->translator->translate($this->key, $this->params, $this->domain);
        }

        // Fallback: interpolate params into the key itself
        if ($this->params === []) {
            return $this->key;
        }

        $replace = [];
        foreach ($this->params as $key => $value) {
            $replace['%' . $key . '%'] = (string) $value;
        }

        return strtr($this->key, $replace);
    }

    /**
     * @return array{key: string, params: array<string, mixed>, domain: string|null}
     */
    public function jsonSerialize(): array
    {
        return [
            'key' => $this->key,
            'params' => $this->params,
            'domain' => $this->domain,
        ];
    }

    /**
     * Create a Text instance from a JSON array (as produced by jsonSerialize).
     *
     * @param array{key: string, params?: array<string, mixed>, domain?: string|null} $data
     */
    public static function fromJson(array $data): self
    {
        return new self(
            key: $data['key'],
            params: $data['params'] ?? [],
            domain: $data['domain'] ?? null,
        );
    }
}
