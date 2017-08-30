<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/interval/license
 * @link       https://www.flipboxfactory.com/software/interval/
 */

namespace flipbox\interval\helpers;

use Craft;
use craft\i18n\Locale;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use yii\helpers\FormatConverter;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class DateIntervalHelper
{
    // Constants
    // =========================================================================

    /**
     * Number of seconds in a minute.
     *
     * @var int
     */
    const SECONDS_MINUTE = 60;

    /**
     * Number of seconds in an hour.
     *
     * @var int
     */
    const SECONDS_HOUR = 3600;

    /**
     * Number of seconds in a day.
     *
     * @var int
     */
    const SECONDS_DAY = 86400;

    /**
     * The number of seconds in a year.
     *
     * @var int
     */
    const SECONDS_YEAR = 31536000;

    // Public Methods
    // =========================================================================

    /**
     * @param int  $seconds     The number of seconds
     *
     * @return string
     */
    public static function secondsToHumanTimeDuration(int $seconds): string
    {
        $secondsInYear = self::SECONDS_YEAR;
        $secondsInDay = self::SECONDS_DAY;
        $secondsInHour = self::SECONDS_HOUR;
        $secondsInMinute = self::SECONDS_MINUTE;

        $years = floor($seconds / $secondsInYear);
        $seconds %= $secondsInYear;

        $days = floor($seconds / $secondsInDay);
        $seconds %= $secondsInDay;

        $hours = floor($seconds / $secondsInHour);
        $seconds %= $secondsInHour;

        $minutes = floor($seconds / $secondsInMinute);
        $seconds %= $secondsInMinute;

        $timeComponents = [];

        if ($years) {
            $timeComponents[] = $years.' '.($years == 1 ? Craft::t('app', 'year') : Craft::t('app', 'years'));
        }

        if ($days) {
            $timeComponents[] = $days.' '.($days == 1 ? Craft::t('app', 'day') : Craft::t('app', 'days'));
        }

        if ($hours) {
            $timeComponents[] = $hours.' '.($hours == 1 ? Craft::t('app', 'hour') : Craft::t('app', 'hours'));
        }

        if ($minutes) {
            $timeComponents[] = $minutes.' '.($minutes == 1 ? Craft::t('app', 'minute') : Craft::t('app', 'minutes'));
        }

        if ($seconds) {
            $timeComponents[] = $seconds.' '.($seconds == 1 ? Craft::t('app', 'second') : Craft::t('app', 'seconds'));
        }

        return implode(', ', $timeComponents);
    }

    /**
     * Returns the interval in a human-friendly string.
     *
     * @param DateInterval $dateInterval
     *
     * @return string
     */
    public static function humanDurationFromInterval(DateInterval $dateInterval): string
    {
        $timeComponents = [];

        if ($dateInterval->invert) {
            $timeComponents[] = '-';
        }

        if ($dateInterval->y) {
            $timeComponents[] = $dateInterval->y.' '.
                ($dateInterval->y == 1 ? Craft::t('app', 'year') : Craft::t('app', 'years'));
        }

        if ($dateInterval->m) {
            $timeComponents[] = $dateInterval->m.' '.
                ($dateInterval->m == 1 ? Craft::t('app', 'month') : Craft::t('app', 'months'));
        }

        if ($dateInterval->d) {
            $timeComponents[] = $dateInterval->d.' '.
                ($dateInterval->d == 1 ? Craft::t('app', 'day') : Craft::t('app', 'days'));
        }

        if ($dateInterval->h) {
            $timeComponents[] = $dateInterval->h.' '.
                ($dateInterval->h == 1 ? Craft::t('app', 'hour') : Craft::t('app', 'hours'));
        }

        if ($dateInterval->i) {
            $timeComponents[] = $dateInterval->i.' '.
                ($dateInterval->i == 1 ? Craft::t('app', 'minute') : Craft::t('app', 'minutes'));
        }

        if ($dateInterval->s) {
            $timeComponents[] = $dateInterval->s.' '.
                ($dateInterval->s == 1 ? Craft::t('app', 'second') : Craft::t('app', 'seconds'));
        }

        return implode('', $timeComponents);
    }

    /**
     * Creates a DateInterval object based on a given number of seconds.
     *
     * @param int $seconds
     *
     * @return DateInterval
     */
    public static function secondsToInterval(int $seconds): DateInterval
    {
        return DateInterval::createFromDateString(
            self::secondsToHumanTimeDuration($seconds)
        );
    }

    /**
     * Returns the number of seconds that a given DateInterval object spans.
     *
     * @param DateInterval $dateInterval
     *
     * @return int
     */
    public static function intervalToSeconds(DateInterval $dateInterval): int
    {
        $reference = new DateTimeImmutable();
        $endTime = $reference->add($dateInterval);

        return $endTime->getTimestamp() - $reference->getTimestamp();
    }

    /**
     * Returns true if interval string is a valid interval.
     *
     * @param string $intervalString
     *
     * @return bool
     */
    public static function isValidIntervalString(string $intervalString): bool
    {
        $interval = DateInterval::createFromDateString($intervalString);

        return $interval->s != 0 ||
            $interval->i != 0 ||
            $interval->h != 0 ||
            $interval->d != 0 ||
            $interval->m != 0 ||
            $interval->y != 0;
    }
}
