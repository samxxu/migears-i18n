<?php

declare(strict_types=1);

namespace MiGears\I18n\Tests;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use MiGears\I18n\ArrayTranslator;
use MiGears\I18n\LocalizedDate;

#[CoversClass(LocalizedDate::class)]
class LocalizedDateTest extends TestCase
{
    /**
     * The full key set the class looks up, rendered in Chinese.
     */
    private function translator(): ArrayTranslator
    {
        return new ArrayTranslator([
            'date.relative.past.moment' => '刚刚',
            'date.relative.future.moment' => '马上',
            'date.relative.past.minute' => '%count% 分钟前',
            'date.relative.future.minute' => '%count% 分钟后',
            'date.relative.past.hour' => '%count% 小时前',
            'date.relative.future.hour' => '%count% 小时后',
            'date.relative.past.day' => '%count% 天前',
            'date.relative.future.day' => '%count% 天后',
            'date.relative.past.week' => '%count% 周前',
            'date.relative.future.week' => '%count% 周后',
            'date.relative.past.month' => '%count% 个月前',
            'date.relative.future.month' => '%count% 个月后',
            'date.relative.past.year' => '%count% 年前',
            'date.relative.future.year' => '%count% 年后',
            'date.weekday.0' => '周日',
            'date.weekday.1' => '周一',
            'date.weekday.2' => '周二',
            'date.weekday.3' => '周三',
            'date.weekday.4' => '周四',
            'date.weekday.5' => '周五',
            'date.weekday.6' => '周六',
            'date.humanize.today' => '今天 %time%',
            'date.humanize.yesterday' => '昨天 %time%',
            'date.humanize.tomorrow' => '明天 %time%',
            'date.humanize.weekday' => '%weekday% %time%',
            'date.humanize.date' => '%month%月%day%日 %time%',
            'date.humanize.dateOtherYear' => '%year%年%month%月%day%日 %time%',
        ]);
    }

    // --- construction and neutral facts ---

    public function testConstructWithNullGivesNow(): void
    {
        $date = new LocalizedDate();
        $now = time();

        $this->assertGreaterThanOrEqual($now - 1, $date->timestamp());
        $this->assertLessThanOrEqual($now + 1, $date->timestamp());
    }

    public function testConstructWithTimestamp(): void
    {
        $date = new LocalizedDate(1704067200);

        $this->assertSame(1704067200, $date->timestamp());
    }

    public function testConstructWithString(): void
    {
        $date = new LocalizedDate('2024-01-15 14:30:00');

        $this->assertSame('2024-01-15', $date->toDateString());
        $this->assertSame('2024-01-15 14:30', $date->toDateTimeString());
    }

    public function testConstructWithDateTimeInterface(): void
    {
        $dt = new DateTimeImmutable('2024-06-15 10:00:00');
        $date = new LocalizedDate($dt);

        $this->assertSame($dt->getTimestamp(), $date->timestamp());
    }

    public function testFromTimestamp(): void
    {
        $date = LocalizedDate::fromTimestamp(1704067200);

        $this->assertSame(1704067200, $date->timestamp());
    }

    public function testFromString(): void
    {
        $date = LocalizedDate::fromString('2024-03-20 08:00:00');

        $this->assertSame('2024-03-20', $date->toDateString());
    }

    public function testFormat(): void
    {
        $date = new LocalizedDate('2024-07-04 14:30:00');

        $this->assertSame('Jul 4, 2024', $date->format('M j, Y'));
    }

    public function testDayOfWeek(): void
    {
        $this->assertSame(1, (new LocalizedDate('2024-01-01'))->dayOfWeek());
        $this->assertSame(0, (new LocalizedDate('2024-01-07'))->dayOfWeek());
    }

    public function testIsTodayYesterdayTomorrow(): void
    {
        $this->assertTrue((new LocalizedDate())->isToday());
        $this->assertTrue((new LocalizedDate(new DateTimeImmutable('yesterday')))->isYesterday());
        $this->assertTrue((new LocalizedDate(new DateTimeImmutable('tomorrow')))->isTomorrow());

        $old = new LocalizedDate('2000-01-01');
        $this->assertFalse($old->isToday());
        $this->assertFalse($old->isYesterday());
        $this->assertFalse($old->isTomorrow());
    }

    // --- timezone ---

    public function testSameInstantRendersPerTimezone(): void
    {
        $utc = new LocalizedDate('2024-01-01 12:00:00', 'UTC');
        $tokyo = $utc->withTimezone('Asia/Tokyo');

        $this->assertSame('2024-01-01 12:00', $utc->toDateTimeString());
        $this->assertSame('2024-01-01 21:00', $tokyo->toDateTimeString());
        $this->assertSame('Asia/Tokyo', $tokyo->timezone()->getName());
    }

    public function testWithTimezoneObject(): void
    {
        $date = new LocalizedDate('2024-01-01 12:00:00', 'UTC');
        $ny = $date->withTimezone(new DateTimeZone('America/New_York'));

        $this->assertSame('2024-01-01 07:00', $ny->toDateTimeString());
    }

    public function testInvalidTimezoneThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new LocalizedDate('2024-01-01', 'Invalid/Timezone');
    }

    public function testWithTimezoneCarriesTranslator(): void
    {
        $source = new DateTimeImmutable('yesterday 14:30:00', new DateTimeZone('UTC'));
        $date = new LocalizedDate($source, 'UTC', $this->translator());

        $rendered = $date->withTimezone('Asia/Tokyo')->humanize();

        // Tokyo runs ahead of UTC, so the wall clock reads 23:30 that day. The
        // point of the test is that the translator survived the conversion:
        // without it, humanize() would have thrown instead of rendering.
        $this->assertStringContainsString('23:30', $rendered);
        $this->assertStringNotContainsString('date.', $rendered);
    }

    // --- relativeParts ---

    public function testRelativePartsUnderAMinute(): void
    {
        $this->assertSame(
            ['direction' => 'past', 'unit' => 'moment', 'count' => 0],
            (new LocalizedDate(time() - 5))->relativeParts(),
        );
    }

    public function testRelativePartsFutureUnderAMinute(): void
    {
        $this->assertSame(
            ['direction' => 'future', 'unit' => 'moment', 'count' => 0],
            (new LocalizedDate(time() + 5))->relativeParts(),
        );
    }

    public function testRelativePartsMinutesAgo(): void
    {
        $parts = (new LocalizedDate(time() - 180))->relativeParts();

        $this->assertSame('past', $parts['direction']);
        $this->assertSame('minute', $parts['unit']);
        $this->assertSame(3, $parts['count']);
    }

    public function testRelativePartsHoursAgo(): void
    {
        $parts = (new LocalizedDate(time() - 7200))->relativeParts();

        $this->assertSame('hour', $parts['unit']);
        $this->assertSame(2, $parts['count']);
    }

    public function testRelativePartsDaysAgo(): void
    {
        $parts = (new LocalizedDate(time() - 86400 * 3))->relativeParts();

        $this->assertSame('day', $parts['unit']);
        $this->assertSame(3, $parts['count']);
    }

    public function testRelativePartsWeeksAgo(): void
    {
        $parts = (new LocalizedDate(time() - 86400 * 14))->relativeParts();

        $this->assertSame('week', $parts['unit']);
        $this->assertSame(2, $parts['count']);
    }

    public function testRelativePartsMonthsAndYearsAgo(): void
    {
        $months = (new LocalizedDate(new DateTimeImmutable('-3 months')))->relativeParts();
        $years = (new LocalizedDate(new DateTimeImmutable('-2 years')))->relativeParts();

        $this->assertSame(['unit' => 'month', 'count' => 3], ['unit' => $months['unit'], 'count' => $months['count']]);
        $this->assertSame(['unit' => 'year', 'count' => 2], ['unit' => $years['unit'], 'count' => $years['count']]);
    }

    public function testRelativePartsFutureDays(): void
    {
        $parts = (new LocalizedDate(time() + 86400 * 5))->relativeParts();

        $this->assertSame('future', $parts['direction']);
        $this->assertSame('day', $parts['unit']);
        $this->assertSame(5, $parts['count']);
    }

    public function testRelativePartsThirtyDaysIsOneMonth(): void
    {
        // 30 days lands in the month branch while the calendar diff can still
        // report 0 months.
        $parts = (new LocalizedDate(time() - 86400 * 30))->relativeParts();

        $this->assertSame('month', $parts['unit']);
        $this->assertSame(1, $parts['count']);
    }

    public function testRelativePartsMonthCountIsNeverZero(): void
    {
        foreach ([7, 28, 29, 30, 31, 45, 89] as $days) {
            $parts = (new LocalizedDate(time() - 86400 * $days))->relativeParts();

            $this->assertGreaterThan(0, $parts['count'], "unit={$parts['unit']}");
        }
    }

    // --- humanizeParts ---

    public function testHumanizePartsToday(): void
    {
        $date = new LocalizedDate();
        $parts = $date->humanizeParts();

        $this->assertSame('today', $parts['kind']);
        $this->assertSame($date->toDateString(), $parts['date']);
        $this->assertTrue($parts['sameYear']);
        $this->assertMatchesRegularExpression('/^\d{2}:\d{2}$/', $parts['time']);
    }

    public function testHumanizePartsYesterdayAndTomorrow(): void
    {
        $yesterday = (new LocalizedDate(new DateTimeImmutable('yesterday 14:30:00')))->humanizeParts();
        $tomorrow = (new LocalizedDate(new DateTimeImmutable('tomorrow 14:30:00')))->humanizeParts();

        $this->assertSame('yesterday', $yesterday['kind']);
        $this->assertSame('14:30', $yesterday['time']);
        $this->assertSame('tomorrow', $tomorrow['kind']);
        $this->assertSame('14:30', $tomorrow['time']);
    }

    public function testHumanizePartsRecentPastIsWeekday(): void
    {
        $parts = (new LocalizedDate(new DateTimeImmutable('-3 days')))->humanizeParts();

        $this->assertSame('weekday', $parts['kind']);
    }

    public function testHumanizePartsFutureBeyondTomorrowIsDate(): void
    {
        // Never "weekday": a weekday name for a future date would read as a day
        // that has already passed.
        $date = new LocalizedDate(time() + 86400 * 3);
        $parts = $date->humanizeParts();

        $this->assertSame('date', $parts['kind']);
        $this->assertSame($date->toDateString(), $parts['date']);
    }

    public function testHumanizePartsOtherYear(): void
    {
        $parts = (new LocalizedDate('2020-03-15 10:00:00'))->humanizeParts();

        $this->assertSame('date', $parts['kind']);
        $this->assertSame('2020-03-15', $parts['date']);
        $this->assertFalse($parts['sameYear']);
        $this->assertSame('10:00', $parts['time']);
    }

    // --- server-side rendering ---

    public function testRelativeRendersThroughTranslator(): void
    {
        $translator = $this->translator();

        $this->assertSame('刚刚', (new LocalizedDate(time() - 5, null, $translator))->relative());
        $this->assertSame('3 分钟前', (new LocalizedDate(time() - 180, null, $translator))->relative());
        $this->assertSame('2 小时前', (new LocalizedDate(time() - 7200, null, $translator))->relative());
        $this->assertSame('5 天后', (new LocalizedDate(time() + 86400 * 5, null, $translator))->relative());
    }

    public function testRelativeRendersMonthBoundary(): void
    {
        $date = new LocalizedDate(time() - 86400 * 30, null, $this->translator());

        $this->assertSame('1 个月前', $date->relative());
    }

    public function testHumanizeRendersToday(): void
    {
        $date = new LocalizedDate(null, null, $this->translator());

        $this->assertMatchesRegularExpression('/^今天 \d{2}:\d{2}$/u', $date->humanize());
    }

    public function testHumanizeRendersYesterdayAndTomorrow(): void
    {
        $translator = $this->translator();

        $this->assertSame('昨天 14:30', (new LocalizedDate(new DateTimeImmutable('yesterday 14:30:00'), null, $translator))->humanize());
        $this->assertSame('明天 14:30', (new LocalizedDate(new DateTimeImmutable('tomorrow 14:30:00'), null, $translator))->humanize());
    }

    public function testHumanizeRendersWeekdayForRecentPast(): void
    {
        $date = new LocalizedDate(new DateTimeImmutable('-3 days 14:30:00'), null, $this->translator());

        $this->assertMatchesRegularExpression('/^周[日一二三四五六] 14:30$/u', $date->humanize());
    }

    public function testHumanizeRendersDateWithinSameYear(): void
    {
        // Two months back may or may not land in the current year depending on
        // when the suite runs, so both shapes are accepted here; the
        // other-year shape is pinned by the next test.
        $date = new LocalizedDate(new DateTimeImmutable('-2 months 14:30:00'), null, $this->translator());

        $this->assertMatchesRegularExpression('/^(\d{4}年)?\d{1,2}月\d{1,2}日 14:30$/u', $date->humanize());
    }

    public function testHumanizeRendersYearForAnotherYear(): void
    {
        $date = new LocalizedDate('2020-03-15 10:00:00', null, $this->translator());

        $this->assertSame('2020年3月15日 10:00', $date->humanize());
    }

    public function testJsonSerializeCarriesRawData(): void
    {
        $date = new LocalizedDate(1704067200, 'Asia/Shanghai', $this->translator());

        $decoded = json_decode(json_encode(['created_at' => $date]), true);

        $this->assertSame([
            'timestamp' => 1704067200,
            'iso' => '2024-01-01T08:00:00+08:00',
            'timezone' => 'Asia/Shanghai',
        ], $decoded['created_at']);
    }

    public function testJsonSerializeDoesNotRenderText(): void
    {
        // A payload must not depend on this server's language.
        $date = new LocalizedDate(time(), null, $this->translator());

        $this->assertIsArray($date->jsonSerialize());
        $this->assertArrayNotHasKey('text', $date->jsonSerialize());
    }

    public function testToStringIsLocaleNeutral(): void
    {
        $date = new LocalizedDate(1704067200, 'Asia/Shanghai', $this->translator());

        $this->assertSame('2024-01-01 08:00', (string) $date);
        $this->assertNotSame($date->humanize(), (string) $date);
    }

    public function testPartsWorkWithoutTranslator(): void
    {
        $date = new LocalizedDate(time() - 86400 * 3);

        $this->assertSame('day', $date->relativeParts()['unit']);
        $this->assertSame('weekday', $date->humanizeParts()['kind']);
    }

    public function testRenderingWithoutTranslatorThrows(): void
    {
        $this->expectException(LogicException::class);
        (new LocalizedDate())->humanize();
    }

    public function testRelativeWithoutTranslatorThrows(): void
    {
        $this->expectException(LogicException::class);
        (new LocalizedDate())->relative();
    }
}
