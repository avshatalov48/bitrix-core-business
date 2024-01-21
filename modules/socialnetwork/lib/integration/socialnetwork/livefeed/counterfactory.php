<?php

namespace Bitrix\Socialnetwork\Integration\SocialNetwork\LiveFeed;

use Bitrix\Socialnetwork\Internals\Space\Counter\ProviderInterface;

class CounterFactory
{
	public static function getLiveFeedCounterProvider(int $userId): ProviderInterface
	{
		$counterProvider = new CounterProvider($userId);

		return ($counterProvider->isEnabled() && $counterProvider->isCounted())
			? $counterProvider
			: new LegacyCounterProvider($userId);
	}
}