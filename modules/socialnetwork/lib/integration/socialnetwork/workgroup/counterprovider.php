<?php

namespace Bitrix\Socialnetwork\Integration\SocialNetwork\WorkGroup;

use Bitrix\Socialnetwork\Internals\Counter;
use Bitrix\Socialnetwork\Internals\Counter\CounterDictionary;
use Bitrix\Socialnetwork\Internals\Space\Counter\Dictionary;
use Bitrix\Socialnetwork\Internals\Space\Counter\ProviderInterface;

class CounterProvider implements ProviderInterface
{
	public function __construct(private int $userId) { }

	public function getTotal(int $spaceId = 0): int
	{
		$result = 0;

		if ($spaceId === 0)
		{
			return $result;
		}

		$metrics = [
			CounterDictionary::COUNTER_WORKGROUP_REQUESTS_IN,
		];

		foreach ($metrics as $metric)
		{
			$result += Counter::getInstance($this->userId)->get($metric, $spaceId)['all'];
		}

		return $result;
	}

	public function getValue(int $spaceId = 0, array $metrics = []): int
	{
		$result = 0;

		if ($spaceId === 0)
		{
			return $result;
		}

		foreach ($metrics as $metric)
		{
			switch ($metric)
			{
				case Dictionary::COUNTERS_WORKGROUP_TOTAL:
					return $this->getTotal($spaceId);
				case Dictionary::COUNTERS_WORKGROUP_REQUEST_OUT:
					$result += Counter::getInstance($this->userId)->get(
						CounterDictionary::COUNTER_WORKGROUP_REQUESTS_OUT,
						$spaceId)['all'];
					break;
			}
		}

		return $result;
	}

	public function getAvailableMetrics(): array
	{
		return [
			Dictionary::COUNTERS_WORKGROUP_TOTAL,
		];
	}
}