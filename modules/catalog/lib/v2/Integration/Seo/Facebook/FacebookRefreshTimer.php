<?php

namespace Bitrix\Catalog\v2\Integration\Seo\Facebook;

use Bitrix\Main\Config\Option;

final class FacebookRefreshTimer
{
	private const OPTION_NAME = 'facebook_last_refreshed_product_timestamp';

	public static function getLastRefreshedTimestamp(): int
	{
		// ToDo return (int)Option::get('catalog', self::OPTION_NAME, 0);
		// last 3 days right now
		return time() - 3 * 24 * 60 * 60;
	}

	public static function setLastRefreshedTimestamp(int $timestamp): void
	{
		Option::set('catalog', self::OPTION_NAME, $timestamp);
	}
}
