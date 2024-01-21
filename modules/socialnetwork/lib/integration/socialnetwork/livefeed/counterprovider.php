<?php

namespace Bitrix\Socialnetwork\Integration\SocialNetwork\LiveFeed;

use Bitrix\Socialnetwork\Internals\LiveFeed\Counter;
use Bitrix\Socialnetwork\Internals\Space\Counter\Dictionary;
use Bitrix\Socialnetwork\Internals\Space\Counter\ProviderInterface;

class CounterProvider implements ProviderInterface
{
	public function __construct(private int $userId) { }

	public function isCounted(): bool
	{
		return Counter::getInstance($this->userId)->isCounted();
	}

	public function isEnabled(): bool
	{
		return Counter\CounterController::isEnabled($this->userId);
	}

	public function getTotal(int $spaceId = 0): int
	{
		return Counter::getInstance($this->userId)->get(Counter\CounterDictionary::COUNTER_TOTAL, $spaceId);
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