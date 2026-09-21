<?php

declare(strict_types=1);

namespace MiGears\I18n;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;
use JsonSerializable;
use LogicException;
use Stringable;

/**
 * A date bound to a viewer's timezone and translator.
 *
 * It reports neutral facts and renders text through the injected translator,
 * so the class itself carries no language. Rendering is for server-side use —
 * humanize() and relative() return finished strings — while jsonSerialize()
 * and __toString() stay raw, so a payload can still be localized by whoever
 * receives it.
 *
 * The translator supplies the message table; this package ships no locale
 * data. The keys it looks up are documented in the README.
 */
final class LocalizedDate implements JsonSerializable, Stringable
{
    private readonly DateTimeImmutable $datetime;

    /**
     * @param DateTimeInterface|int|string|null $input Timestamp, datetime string, DateTime, or null for "now"
     * @param DateTimeZone|string|null $timezone Timezone the viewer should see (null uses default)
     * @param TranslatorInterface|null $translator Translator used by relative() and humanize()
     */
    public function __construct(
        DateTimeInterface|int|string|null $input = null,
        DateTimeZone|string|null $timezone = null,
        private readonly ?TranslatorInterface $translator = null,
    ) {
        $tz = $this->resolveTimezone($timezone);

        $dt = match (true) {
            $input === null => new DateTimeImmutable('now', $tz),
            $input instanceof DateTimeInterface => DateTimeImmutable::createFromInterface($input),
            is_int($input) => (new DateTimeImmutable('now', $tz))->setTimestamp($input),
            is_string($input) => new DateTimeImmutable($input, $tz),
        };

        if ($tz !== null && !($input instanceof DateTimeInterface && $input->getTimezone()->getName() === $tz->getName())) {
            $dt = $dt->setTimezone($tz);
        }

        $this->datetime = $dt;
    }

    /** Create from a Unix timestamp. */
    public static function fromTimestamp(
        int $timestamp,
        DateTimeZone|string|null $timezone = null,
        ?TranslatorInterface $translator = null,
    ): self {
        return new self($timestamp, $timezone, $translator);
    }

    /** Create from a datetime string. */
    public static function fromString(
        string $datetime,
        DateTimeZone|string|null $timezone = null,
        ?TranslatorInterface $translator = null,
    ): self {
        return new self($datetime, $timezone, $translator);
    }

    /** Get the underlying DateTimeImmutable instance. */
    public function toDateTime(): DateTimeImmutable
    {
        return $this->datetime;
    }

    /** Get the Unix timestamp. */
    public function timestamp(): int
    {
        return $this->datetime->getTimestamp();
    }

    /** Format as a locale-neutral date string: Y-m-d */
    public function toDateString(): string
    {
        return $this->datetime->format('Y-m-d');
    }

    /** Format as a locale-neutral datetime string: Y-m-d H:i */
    public function toDateTimeString(): string
    {
        return $this->datetime->format('Y-m-d H:i');
    }

    /** Format using an arbitrary date pattern. */
    public function format(string $pattern): string
    {
        return $this->datetime->format($pattern);
    }

    /** Day of week as integer (0 = Sunday, 6 = Saturday). */
    public function dayOfWeek(): int
    {
        return (int) $this->datetime->format('w');
    }

    /** Whether this date is today in its own timezone. */
    public function isToday(): bool
    {
        $today = new DateTimeImmutable('today', $this->datetime->getTimezone());
        return $this->datetime >= $today && $this->datetime < $today->modify('+1 day');
    }

    /** Whether this date is yesterday in its own timezone. */
    public function isYesterday(): bool
    {
        $yesterday = new DateTimeImmutable('yesterday', $this->datetime->getTimezone());
        return $this->datetime >= $yesterday && $this->datetime < $yesterday->modify('+1 day');
    }

    /** Whether this date is tomorrow in its own timezone. */
    public function isTomorrow(): bool
    {
        $tomorrow = new DateTimeImmutable('tomorrow', $this->datetime->getTimezone());
        return $this->datetime >= $tomorrow && $this->datetime < $tomorrow->modify('+1 day');
    }

    /**
     * Render the distance to now, e.g. "2 小时前".
     *
     * @throws LogicException when no translator was supplied
     */
    public function relative(): string
    {
        $parts = $this->relativeParts();

        return $this->translate(
            "date.relative.{$parts['direction']}.{$parts['unit']}",
            ['count' => $parts['count']],
        );
    }

    /**
     * Render a friendly timestamp, e.g. "今天 10:30".
     *
     * @throws LogicException when no translator was supplied
     */
    public function humanize(): string
    {
        $parts = $this->humanizeParts();
        $params = ['time' => $parts['time']];

        if ($parts['kind'] === 'weekday') {
            $params['weekday'] = $this->translate("date.weekday.{$this->dayOfWeek()}");
        }

        if ($parts['kind'] === 'date') {
            [$year, $month, $day] = explode('-', $parts['date']);

            $params += [
                'date' => $parts['date'],
                // Numeric parts are passed unpadded so each message table decides
                // its own padding: "3月15日" is idiomatic, "03月15日" is not.
                'year' => (int) $year,
                'month' => (int) $month,
                'day' => (int) $day,
            ];
        }

        $otherYear = $parts['kind'] === 'date' && !$parts['sameYear'] ? 'OtherYear' : '';

        return $this->translate("date.humanize.{$parts['kind']}{$otherYear}", $params);
    }

    /**
     * Classify the distance to now, in language-free terms.
     *
     * Anything under a minute reports unit "moment" with a count of 0.
     *
     * @return array{direction: 'past'|'future', unit: 'moment'|'minute'|'hour'|'day'|'week'|'month'|'year', count: int}
     */
    public function relativeParts(): array
    {
        $now = new DateTimeImmutable('now', $this->datetime->getTimezone());
        $diff = $this->datetime->diff($now);
        $secs = abs($now->getTimestamp() - $this->datetime->getTimestamp());

        // Months and years come from $diff, which counts calendar months. A gap
        // of roughly 30 days reaches the month branch while $diff->m can still
        // read 0, so that count is floored at 1.
        [$unit, $count] = match (true) {
            $secs < 60 => ['moment', 0],
            $secs < 3600 => ['minute', (int) floor($secs / 60)],
            $secs < 86400 => ['hour', (int) floor($secs / 3600)],
            $secs < 604800 => ['day', (int) floor($secs / 86400)],
            $secs < 2592000 => ['week', (int) floor($secs / 604800)],
            $diff->y === 0 => ['month', max(1, $diff->m)],
            default => ['year', $diff->y],
        };

        return [
            'direction' => $this->datetime < $now ? 'past' : 'future',
            'unit' => $unit,
            'count' => $count,
        ];
    }

    /**
     * Classify this moment into the bucket a friendly timestamp would use.
     *
     * "weekday" is reserved for the recent past: for a date past tomorrow a
     * bare weekday name would read like a day that has already been, so those
     * report "date" instead. A renderer therefore never has to infer direction
     * for itself. The weekday index, when needed, comes from dayOfWeek().
     *
     * @return array{kind: 'today'|'yesterday'|'tomorrow'|'weekday'|'date', date: string, sameYear: bool, time: string}
     */
    public function humanizeParts(): array
    {
        $now = new DateTimeImmutable('now', $this->datetime->getTimezone());
        $diffDays = (int) floor(abs($now->getTimestamp() - $this->datetime->getTimestamp()) / 86400);

        $kind = match (true) {
            $this->isToday() => 'today',
            $this->isYesterday() => 'yesterday',
            $this->isTomorrow() => 'tomorrow',
            $this->datetime <= $now && $diffDays <= 6 => 'weekday',
            default => 'date',
        };

        return [
            'kind' => $kind,
            'date' => $this->datetime->format('Y-m-d'),
            'sameYear' => $this->isSameYear($now),
            'time' => $this->datetime->format('H:i'),
        ];
    }

    /** Return a new instance converted to the given timezone, translator carried over. */
    public function withTimezone(DateTimeZone|string $timezone): self
    {
        $tz = $this->resolveTimezone($timezone);
        return new self($this->datetime->setTimezone($tz), $tz, $this->translator);
    }

    /** Get the current timezone. */
    public function timezone(): DateTimeZone
    {
        return $this->datetime->getTimezone();
    }

    /**
     * Serialize as raw data, never as rendered text.
     *
     * A payload travels to clients that localize with their own resources, and
     * a rendered string would bake this server's language into it. Use
     * humanize() or relative() for server-side rendering instead.
     *
     * @return array{timestamp: int, iso: string, timezone: string}
     */
    public function jsonSerialize(): array
    {
        return [
            'timestamp' => $this->timestamp(),
            'iso' => $this->datetime->format('c'),
            'timezone' => $this->datetime->getTimezone()->getName(),
        ];
    }

    /** Locale-neutral string form, so echoing never leaks a language. */
    public function __toString(): string
    {
        return $this->toDateTimeString();
    }

    /**
     * @param array<string, mixed> $params
     */
    private function translate(string $key, array $params = []): string
    {
        if ($this->translator === null) {
            throw new LogicException('LocalizedDate needs a translator to render text');
        }

        return $this->translator->translate($key, $params);
    }

    private function isSameYear(DateTimeImmutable $other): bool
    {
        return $this->datetime->format('Y') === $other->format('Y');
    }

    private function resolveTimezone(DateTimeZone|string|null $timezone): ?DateTimeZone
    {
        if ($timezone === null) {
            return null;
        }
        if ($timezone instanceof DateTimeZone) {
            return $timezone;
        }
        $tz = @timezone_open($timezone);
        if ($tz === false) {
            throw new InvalidArgumentException("Invalid timezone: {$timezone}");
        }
        return $tz;
    }
}
