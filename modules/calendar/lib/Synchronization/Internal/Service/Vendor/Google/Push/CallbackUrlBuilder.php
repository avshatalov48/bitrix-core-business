<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Push;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;

class CallbackUrlBuilder
{
	private const EXTERNAL_LINK = 'https://www.bitrix24.com/controller/google_calendar_push.php?target_host=';

	/**
	 * Builds an url where notifications from Google are delivered for some push channel.
	 *
	 * @return string
	 */
	public static function buildUrl(): string
	{
		if (defined('BX24_HOST_NAME') && BX24_HOST_NAME)
		{
			return self::EXTERNAL_LINK . BX24_HOST_NAME;
		}

		if (defined('SITE_SERVER_NAME') && SITE_SERVER_NAME)
		{
			$host = SITE_SERVER_NAME;
		}
		else
		{
			$host = Option::get('main', 'server_name', Context::getCurrent()->getRequest()->getHttpHost());
		}

		return 'https://' . $host . '/bitrix/tools/calendar/push.php';
	}
}
