<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/interval/license
 * @link       https://www.flipboxfactory.com/software/interval/
 */

namespace flipbox\interval\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\enums\PeriodType;
use craft\helpers\ArrayHelper;
use craft\helpers\DateTimeHelper;
use craft\helpers\StringHelper;
use DateInterval;
use yii\db\Schema;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Interval extends Field
{

    public $default = 0;

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('interval', 'Interval');
    }

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_BIGINT;
    }

    /**
     * @param DateInterval $value
     *
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        // Is inverted
        $invert = $value->invert;

        // Remove invert flag
        $value->invert = 0;

        // This will put the value in the greatest denominator
        $value = DateInterval::createFromDateString(
            DateTimeHelper::secondsToHumanTimeDuration(
                DateTimeHelper::intervalToSeconds($value)
            )
        );

        list($amount, $period) = explode(
            ' ',
            DateTimeHelper::humanDurationFromInterval($value)
        );

        // Ensure plural
        $period = StringHelper::removeRight($period, 's') . 's';

        // If invert adjust amount
        if ($invert) {
            $amount = $amount * -1;
        }

        return Craft::$app->getView()->renderTemplate(
            'interval/_components/fieldtypes/Interval/input',
            [
                'field' => $this,
                'amount' => $amount,
                'period' => $period,
                'periods' => [
                    PeriodType::Seconds => Craft::t('app', 'Seconds'),
                    PeriodType::Minutes => Craft::t('app', 'Minutes'),
                    PeriodType::Hours => Craft::t('app', 'Hours'),
                    PeriodType::Days => Craft::t('app', 'Days'),
                    PeriodType::Months => Craft::t('app', 'Months'),
                    PeriodType::Years => Craft::t('app', 'Years'),
                ]
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getStaticHtml($value, ElementInterface $element): string
    {
        // Just return the input HTML with disabled inputs by default
        Craft::$app->getView()->startJsBuffer();
        $inputHtml = $this->getInputHtml($value, $element);
        $inputHtml = preg_replace('/<(?:input|textarea|select)\s[^>]*/i', '$0 disabled', $inputHtml);
        Craft::$app->getView()->clearJsBuffer();

        return $inputHtml;
    }

    /**
     * @inheritdoc
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        return parent::serializeValue(
            DateTimeHelper::intervalToSeconds($value),
            $element
        );
    }

    /**
     * @inheritdoc
     */
    public function getSearchKeywords($value, ElementInterface $element): string
    {
        return DateTimeHelper::humanDurationFromInterval($value);
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        // Seconds
        if (is_numeric($value)) {
            return $this->toDateIntervalFromSeconds($value);
        }

        // Human readable
        if (is_string($value)) {
            return DateInterval::createFromDateString($value);
        }

        // Human readable (array)
        if (is_array($value)) {
            return $this->toDateIntervalFromHumanReadable($value);
        }

        return DateTimeHelper::secondsToInterval($this->default);
    }

    /**
     * @param int $seconds
     * @return \DateInterval
     */
    private function toDateIntervalFromSeconds(int $seconds)
    {
        $invert = false;

        if ($seconds < 0) {
            $invert = true;
            $seconds = $seconds * -1;
        }

        $dateInterval = DateTimeHelper::secondsToInterval($seconds);

        if ($invert) {
            $dateInterval->invert = 1;
        }

        return $dateInterval;
    }

    /**
     * @param array $interval
     * @return DateInterval
     */
    private function toDateIntervalFromHumanReadable(array $interval = ['amount' => 0, 'period' => ''])
    {

        $invert = false;

        $period = ArrayHelper::getValue($interval, 'amount');

        if ($period < 0) {
            $invert = true;
            $period = $period * -1;
        }

        $dateInterval = DateInterval::createFromDateString(
            StringHelper::toString(
                [
                    $period,
                    ArrayHelper::getValue($interval, 'period')
                ],
                ' '
            )
        );

        if ($invert) {
            $dateInterval->invert = 1;
        }

        return $dateInterval;
    }
}
