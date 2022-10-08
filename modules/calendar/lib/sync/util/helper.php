<?php

namespace Bitrix\Calendar\Sync\Util;

use Bitrix\Main\Application;

class Helper
{
	/**
	 *
	 * @return string
	 */
	public static function getDomain(): string
	{
		if (\CCalendar::isBitrix24())
		{
			return 'https://bitrix24.com';
		}

		if (defined('BX24_HOST_NAME') && BX24_HOST_NAME)
		{
			return "https://" . BX24_HOST_NAME;
		}

		$server = Application::getInstance()->getContext()->getServer();

		return "https://" . $server['HTTP_HOST'];
	}
}