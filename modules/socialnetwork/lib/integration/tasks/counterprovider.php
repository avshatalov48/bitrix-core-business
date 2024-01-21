<?php

namespace Bitrix\Socialnetwork\Integration\Tasks;

use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Internals\Space\Counter\Dictionary;
use Bitrix\Socialnetwork\Internals\Space\Counter\ProviderInterface;
use Bitrix\Tasks\Internals\Counter;

class CounterProvider implements ProviderInterface
{
	public function __construct(private int $userId) { }

	public function getTotal(int $spaceId = 0): int
	{
		$result = 0;

		if (!$this->isTasksModuleAvailable())
		{
			return $result;
		}

		return Counter::getInstance($this->userId)->get(Counter\CounterDictionary::COUNTER_MEMBER_TOTAL, $spaceId);
	}

	public function getValue(int $spaceId = 0, array $metrics = []): int
	{
		$result = 0;

		if (!$this->isTasksModuleAvailable())
		{
			return $result;
		}

		foreach ($metrics as $metric)
		{
			switch ($metric)
			{
				case Dictionary::COUNTERS_TASKS_TOTAL:
					return $this->getTotal($spaceId);
					//TODO: other cases...
			}
		}

		return $result;
	}

	public function getAvailableMetrics(): array
	{
		return [
			Dictionary::COUNTERS_TASKS_TOTAL,
		];
	}

	private function isTasksModuleAvailable(): bool
	{
		return Loader::includeModule('tasks');
	}
}