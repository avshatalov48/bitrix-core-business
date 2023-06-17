<?php

namespace Bitrix\Calendar\Sharing;

use Bitrix\Main\Result;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\CalendarMobile;

class SharingFeature
{
	private const SHARING_OPTION_NAME = 'isSharingEnabled';
	private const OPTION_ENABLED = 'Y';
	private const OPTION_DISABLED = 'N';

	/**
	 * enables calendar sharing feature
	 * @return Result
	 */
	public static function enable(): Result
	{
		$result = new Result();
		Option::set('calendar', self::SHARING_OPTION_NAME, self::OPTION_ENABLED);
		if (Loader::includeModule('calendarmobile'))
		{
			$r = CalendarMobile\JSComponent::enable();
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * disables calendar sharing feature
	 * @return Result
	 */
	public static function disable(): Result
	{
		$result = new Result();
		Option::set('calendar', self::SHARING_OPTION_NAME, self::OPTION_DISABLED);
		if (Loader::includeModule('calendarmobile'))
		{
			$r = CalendarMobile\JSComponent::disable();
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * returns true if calendar sharing feature enabled, false otherwise
	 * @return bool
	 */
	public static function isEnabled(): bool
	{
		return true;
	}
}
