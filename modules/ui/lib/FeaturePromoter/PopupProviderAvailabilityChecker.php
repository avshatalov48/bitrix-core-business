<?php

namespace Bitrix\UI\FeaturePromoter;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

class PopupProviderAvailabilityChecker
{
	private const MODULE_ID = 'ui';
	private const ACCESS_OPTION_NAME = 'info-helper-popup-provider';
	private const AVAILABLE_ZONES = ['ru', 'by'];
	private const UNDEFINED_OPTION_STATUS = 'undefined';
	private const DISABLED_OPTION_STATUS = 'N';
	private const ENABLED_OPTION_STATUS = 'Y';

	public function isAvailable(): bool
	{
		if (!Loader::includeModule('bitrix24'))
		{
			return false;
		}

		if ($this->isUnavailableByOption())
		{
			return false;
		}

		return $this->isAvailableByRegion() || $this->isAvailableByOption();
	}

	private function isAvailableByOption(): bool
	{
		return Option::get(self::MODULE_ID, self::ACCESS_OPTION_NAME, self::UNDEFINED_OPTION_STATUS)
			=== self::ENABLED_OPTION_STATUS;
	}

	private function isUnavailableByOption(): bool
	{
		return Option::get(self::MODULE_ID, self::ACCESS_OPTION_NAME, self::UNDEFINED_OPTION_STATUS)
			=== self::DISABLED_OPTION_STATUS;
	}

	private function isAvailableByRegion(): bool
	{
		return in_array(\CBitrix24::getPortalZone(), self::AVAILABLE_ZONES);
	}
}