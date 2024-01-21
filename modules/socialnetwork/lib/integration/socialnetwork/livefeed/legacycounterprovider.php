<?php

namespace Bitrix\Socialnetwork\Integration\SocialNetwork\LiveFeed;

use Bitrix\Socialnetwork\Internals\Space\Counter\Dictionary;
use Bitrix\Socialnetwork\Internals\Space\Counter\ProviderInterface;

class LegacyCounterProvider implements ProviderInterface
{
	public function __construct(private int $userId) { }

	public function getTotal(int $spaceId = 0): int
	{
		if ($spaceId === 0)
		{
			return \CUserCounter::GetValueByUserID($this->userId, SITE_ID, \CUserCounter::LIVEFEED_CODE);
		}

		// group livefeed
		return 0;
	}

	public function getValue(int $spaceId = 0, array $metrics = []): int
	{
		$result = 0;

		foreach ($metrics as $metric)
		{
			switch ($metric)
			{
				case Dictionary::COUNTERS_LIVEFEED_TOTAL:
					return $this->getTotal($spaceId);
				//TODO: other cases...
			}
		}

		return $result;
	}

	public function getAvailableMetrics(): array
	{
		return [
			Dictionary::COUNTERS_LIVEFEED_TOTAL,
		];
	}
}