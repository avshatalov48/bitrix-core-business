<?php
namespace Bitrix\Calendar\Integration;

use Bitrix\Calendar\Integration\Bitrix24\FeatureDictionary;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ModuleManager;
use \Bitrix\Main\Config\Option;

/**
 * Class Bitrix24Manager
 *
 * Required in Bitrix24 context. Provides information about the license and supported features.
 * @package Bitrix\Calendar\Integration
 */
class Bitrix24Manager
{
	const EVENT_AMOUNT = "event_with_planner_amount";

	//region Methods
	/**
	 * Check if current manager enabled.
	 * @return bool
	 */
	public static function isEnabled()
	{
		return ModuleManager::isModuleInstalled('bitrix24');
	}


	/**
	 * Check if specified feature is enabled
	 * @param string $featureId Name of feature
	 * @return bool
	 * @throws LoaderException
	 */
	public static function isFeatureEnabled($featureId): bool
	{
		if (!(ModuleManager::isModuleInstalled('bitrix24') && Loader::includeModule('bitrix24')))
		{
			return true;
		}

		return \Bitrix\Bitrix24\Feature::isFeatureEnabled($featureId);
	}

	/**
	 * Check if specified feature is promo for current tariff
	 * @param $featureId
	 * @return bool
	 * @throws LoaderException
	 */
	public static function isPromoFeatureEnabled($featureId): bool
	{
		if (!(ModuleManager::isModuleInstalled('bitrix24') && Loader::includeModule('bitrix24')))
		{
			return false;
		}

		return \Bitrix\Bitrix24\Feature::isPromoEditionAvailableByFeature($featureId);
	}

	/**
	 * Get variable value.
	 * @param string $name Name of variable
	 * @return mixed|null
	 * @throws LoaderException
	 */
	public static function getVariable($name)
	{
		if (!(ModuleManager::isModuleInstalled('bitrix24') && Loader::includeModule('bitrix24')))
		{
			return null;
		}

		return \Bitrix\Bitrix24\Feature::getVariable($name);
	}

	/**
	 * Check if "planner" feature is enabled
	 * @return bool
	 * @throws LoaderException
	 */
	public static function isPlannerFeatureEnabled(): bool
	{
		if (self::isFeatureEnabled(FeatureDictionary::CALENDAR_EVENTS_WITH_PLANNER))
		{
			return true;
		}

		$eventsLimit = self::getEventWithPlannerLimit();

		return $eventsLimit === -1 || self::getEventsAmount() <= $eventsLimit;
	}

	/**
	 * Check if specified feature is enabled
	 * @return int
	 * @throws LoaderException
	 */
	public static function getEventWithPlannerLimit(): int
	{
		$limit = self::getVariable(FeatureDictionary::CALENDAR_EVENTS_WITH_PLANNER);
		if (is_null($limit))
		{
			$limit = -1;
		}

		return $limit;
	}

	/**
	 * Returns events amount
	 * @return int
	 */
	public static function getEventsAmount(): int
	{
		return Option::get('calendar', self::EVENT_AMOUNT, 0);
	}

	/**
	 * Sets events amount
	 * @param int $value amount of events
	 * @return void
	 * @throws ArgumentOutOfRangeException
	 */
	public static function setEventsAmount($value = 0): void
	{
		if (ModuleManager::isModuleInstalled('bitrix24'))
		{
			Option::set('calendar', self::EVENT_AMOUNT, $value);
		}
	}

	/**
	 * Increase events amount
	 * @return void
	 * @throws ArgumentOutOfRangeException
	 */
	public static function increaseEventsAmount(): void
	{
		self::setEventsAmount(self::getEventsAmount() + 1);
	}
}
?>
