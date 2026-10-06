<?php

declare(strict_types=1);

namespace MiGears\I18n;

/**
 * Convenience factory for Text objects bound to this translator.
 *
 * Mixed into every TranslatorInterface implementation so callers can build a
 * ready-to-render Text without a separate setTranslator() call:
 *
 *   $text = $translator->newText('HELLO_USER', ['user' => 'Alice']);
 *   // ... later
 *   echo $text; // "Hello, Alice"
 *
 * Text itself stays usable without a translator; this only spares the common
 * case of deferring translation and injecting the translator immediately after.
 *
 * @phpstan-require-implements TranslatorInterface
 */
trait CreatesText
{
    /**
     * @param array<string, mixed> $params interpolation parameters; values must be
     *                                     scalar, Stringable or null
     * @param string|null          $domain optional text domain
     *
     * @throws \InvalidArgumentException if a parameter value cannot be rendered as text
     */
    public function newText(string $key, array $params = [], ?string $domain = null): Text
    {
        return (new Text($key, $params, $domain))->setTranslator($this);
    }
}
