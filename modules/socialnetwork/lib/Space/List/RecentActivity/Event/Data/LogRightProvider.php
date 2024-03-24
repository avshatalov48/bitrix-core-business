<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity\Event\Data;

use Bitrix\Socialnetwork\Item\LogRight;

final class LogRightProvider
{
	private static array $cache = [];

	public function get(int $sonetLogId): array
	{
		if (!array_key_exists($sonetLogId, self::$cache))
		{
			self::$cache[$sonetLogId] = LogRight::get($sonetLogId);
		}

		return self::$cache[$sonetLogId];
	}
}