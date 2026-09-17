<?php

declare(strict_types=1);

namespace MiGears\I18n;

interface TranslatorInterface
{
    /**
     * Translate a message key with optional variable interpolation.
     *
     * @param string               $key    translation key or fallback text
     * @param array<string, mixed> $params variables to interpolate into the translated string
     * @param string|null          $domain optional text domain for multi-domain setups
     */
    public function translate(string $key, array $params = [], ?string $domain = null): string;
}
