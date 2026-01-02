<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot;

use Bitrix\Bitrix24\Feature;
use Bitrix\Main\Loader;
use Bitrix\AI\Tuning;
use Bitrix\Landing;

/**
 * Some functions for AI sites
 */
class Manager
{
	/**
	 * Check is feature available (by option, tariff etc.)
	 *
	 * @return bool
	 */
	public static function isAvailable(): bool
	{
		if (Landing\Manager::getZone() === 'cn')
		{
			return false;
		}

		return Loader::includeModule('ai');
	}

	public static function isFeatureEnabled(): bool
	{
		if (Loader::includeModule('bitrix24'))
		{
			return Feature::isFeatureEnabled('landing_allow_ai_sites');
		}

		return true;
	}

	/**
	 * Check is feature enabled in settings
	 * @return bool
	 */
	public static function isActive(): bool
	{
		if (!static::isAvailable())
		{
			return false;
		}

		$manager = new Tuning\Manager();
		$item = $manager->getItem(Landing\Connector\Ai::TUNING_CODE_ALLOW_SITE_COPILOT);

		return $item ? $item->getValue() : false;
	}

	public static function getLimitSliderCode(): string
	{
		return 'limit_copilot';
	}

	public static function getUnactiveSliderCode(): string
	{
		return 'limit_copilot_off';
	}
}