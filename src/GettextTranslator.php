<?php

declare(strict_types=1);

namespace MiGears\I18n;

use RuntimeException;

/**
 * Gettext-based translator using PHP's native gettext extension.
 *
 * Requires the gettext PHP extension (ext-gettext).
 * This is an optional driver; prefer ArrayTranslator for simpler setups.
 */
class GettextTranslator implements TranslatorInterface
{
    private readonly string $defaultDomain;

    /**
     * @param string $defaultDomain default text domain
     * @param string $locale        locale string (e.g. "zh_CN.UTF-8")
     * @param string $directory     path to locale directory containing LC_MESSAGES
     * @param string $codeset       character encoding (default: UTF-8)
     *
     * @throws RuntimeException if gettext extension is not available
     */
    public function __construct(
        string $defaultDomain = 'messages',
        string $locale = 'en_US.UTF-8',
        string $directory = './locale',
        string $codeset = 'UTF-8',
    ) {
        if (!function_exists('gettext')) {
            throw new RuntimeException('Gettext extension is not available. Install ext-gettext or use ArrayTranslator instead.');
        }

        $this->defaultDomain = $defaultDomain;

        putenv("LANG={$locale}");
        putenv("LC_ALL={$locale}");
        setlocale(LC_ALL, $locale);

        bindtextdomain($defaultDomain, $directory);
        bind_textdomain_codeset($defaultDomain, $codeset);
        textdomain($defaultDomain);
    }

    /**
     * Register an additional text domain.
     */
    public function addDomain(string $domain, string $directory, string $codeset = 'UTF-8'): void
    {
        bindtextdomain($domain, $directory);
        bind_textdomain_codeset($domain, $codeset);
    }

    /**
     * @param array<string, mixed> $params
     */
    public function translate(string $key, array $params = [], ?string $domain = null): string
    {
        $domain ??= $this->defaultDomain;
        $translation = dgettext($domain, $key);

        if ($params === []) {
            return $translation;
        }

        return $this->interpolate($translation, $params);
    }

    /**
     * Interpolate %placeholders% in the translated string.
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
