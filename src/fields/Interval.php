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
use craft\base\PreviewableFieldInterface;
use craft\enums\PeriodType;
use craft\helpers\ArrayHelper;
use flipbox\interval\helpers\DateIntervalHelper;
use craft\helpers\StringHelper;
use DateInterval;
use yii\db\Schema;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Interval extends Field implements PreviewableFieldInterface
{

    /**
     * The default value
     *
     * @var int
     */
    public $defaultAmount = 0;

    /**
     * The default period
     *
     * @var int
     */
    public $defaultPeriod = PeriodType::Days;

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

        $amount = '';
        $period = '';

        if (!$this->isFresh($element) && $humanInterval = $this->toHumanTimeDurationWithDefault($value)) {
            list($amount, $period) = explode(
                ' ',
                $humanInterval
            );
        }

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
                    '' => '',
                    PeriodType::Seconds => Craft::t('app', 'Seconds'),
                    PeriodType::Minutes => Craft::t('app', 'Minutes'),
                    PeriodType::Hours => Craft::t('app', 'Hours'),
                    PeriodType::Days => Craft::t('app', 'Days'),
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
        return $this->getTableAttributeHtml($value, $element);
    }

    /**
     * @inheritdoc
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        return parent::serializeValue(
            DateIntervalHelper::intervalToSeconds($value),
            $element
        );
    }

    /**
     * @inheritdoc
     */
    public function getSearchKeywords($value, ElementInterface $element): string
    {
        return $this->getTableAttributeHtml($value, $element);
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

        // Fresh -> use default
        if ($this->isFresh($element)) {
            DateIntervalHelper::secondsToInterval($this->defaultAmount);
        }

        return DateIntervalHelper::secondsToInterval(0);
    }

    /**
     * @param mixed $value
     * @param ElementInterface $element
     * @return string
     */
    public function getTableAttributeHtml($value, ElementInterface $element): string
    {
        return StringHelper::toTitleCase(
            $this->toHumanTimeDurationWithDefault($value)
        );
    }

    /**
     * @inheritdoc
     */
    public function isEmpty($value): bool
    {
        return parent::isEmpty($value) || DateIntervalHelper::intervalToSeconds($value) === 0;
    }

    /**
     * @param DateInterval $dateInterval
     * @return string
     */
    private function toHumanTimeDurationWithDefault(DateInterval $dateInterval): string
    {
        if (DateIntervalHelper::intervalToSeconds($dateInterval) === 0) {
            return '0 ' . $this->defaultPeriod;
        }

        return DateIntervalHelper::humanDurationFromInterval($dateInterval);
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

        $dateInterval = DateIntervalHelper::secondsToInterval($seconds);

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

        $amount = (int)ArrayHelper::getValue($interval, 'amount');

        if ($amount < 0) {
            $invert = true;
            $amount = $amount * -1;
        }

        $period = ArrayHelper::getValue(
            $interval,
            'period',
            $this->defaultPeriod
        );

        $dateInterval = DateInterval::createFromDateString(
            StringHelper::toString(
                [
                    $amount,
                    $period
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
