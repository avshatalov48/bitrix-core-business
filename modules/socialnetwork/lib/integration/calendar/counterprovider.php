<?php

namespace Bitrix\Socialnetwork\Integration\Calendar;

use Bitrix\Calendar\Ui\CountersManager;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Internals\Space\Counter\Dictionary;
use Bitrix\Socialnetwork\Internals\Space\Counter\ProviderInterface;

class CounterProvider implements ProviderInterface
{
	public function __construct(private int $userId) { }

	public function getTotal(int $spaceId = 0): int
	{
		$result = 0;

		if (!$this->isCalendarModuleAvailable())
		{
			return $result;
		}

		if ($spaceId !== 0)
		{
			return $result;
		}

		$counters = CountersManager::getValues($this->userId);

		if (isset($counters['invitation']['value']))
		{
			return (int)$counters['invitation']['value'];
		}

		return $result;
	}

	public function getValue(int $spaceId = 0, array $metrics = []): int
	{
		$result = 0;

		if (!$this->isCalendarModuleAvailable())
		{
			return $result;
		}

		foreach ($metrics as $metric)
		{
			switch ($metric)
			{
				case Dictionary::COUNTERS_CALENDAR_TOTAL:
					return $this->getTotal($spaceId);
					//TODO: other cases ...
			}
		}

		return $result;
	}

	public function getAvailableMetrics(): array
	{
		return [
			Dictionary::COUNTERS_CALENDAR_TOTAL,
		];
	}

	private function isCalendarModuleAvailable(): bool
	{
		return Loader::includeModule('calendar');
	}
}