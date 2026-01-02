<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Integration\Calendar;

use Bitrix\Main\Loader;

class Calendar
{
	public static function getSettings(): array
	{
		if (!Loader::includeModule('calendar'))
		{
			return [];
		}

		return \CCalendar::getSettings(['getDefaultForEmpty' => false]);
	}
}
