<?php

namespace Bitrix\Calendar\Internals\Counter\State;

use Bitrix\Calendar\Internals\Counter\CounterDictionary;
use Bitrix\Calendar\OpenEvents\Provider\CategoryMuteProvider;

class State
{
	private Loader $loader;

	protected int $userId;
	protected array $counters = [];
	protected CategoryMuteProvider $muteProvider;

	public function __construct(int $userId, Loader $loader)
	{
		$this->userId = $userId;
		$this->loader = $loader;
		$this->muteProvider = new CategoryMuteProvider($this->userId);
		$this->init();
	}

	public function init(): void
	{
		$this->counters = $this->getCountersEmptyState();
		$this->loadCounters();
	}

	public function get(string $metaProp = CounterDictionary::META_PROP_ALL): array
	{
		return $this->counters[$metaProp] ?? [];
	}

	private function loadCounters(): void
	{
		$categoryIds = [];
		foreach ($this->loader->getRawCounters() as $counter)
		{
			$type = $counter['TYPE'];
			$parentId = (int)$counter['PARENT_ID'];

			if ($type === CounterDictionary::SCORER_OPEN_EVENT)
			{
				$categoryIds[] = $parentId;
			}
		}

		$mutedCategories = $this->muteProvider->getByCategoryIds($categoryIds);

		foreach ($this->loader->getRawCounters() as $counter)
		{
			$type = $counter['TYPE'];
			$value = (int)$counter['VALUE'];
			$eventId = (int)$counter['EVENT_ID'];
			$parentId = (int)$counter['PARENT_ID'];

			// pre-calculating open events
			$openEventMetaProp = CounterDictionary::META_PROP_OPEN_EVENTS;
			if ($type === CounterDictionary::SCORER_OPEN_EVENT)
			{
				// for specific category
				$this->counters[$openEventMetaProp]['category'][$parentId] ??= 0;
				$this->counters[$openEventMetaProp]['category'][$parentId] += $value;

				// for all categories
				$isMuted = $mutedCategories[$parentId] ?? false;
				if (!$isMuted)
				{
					$this->counters[$openEventMetaProp]['total'] += $value;
				}

				// for specific event
				$this->counters[CounterDictionary::META_PROP_NEW_EVENTS][$eventId] = $value;
			}
		}
	}

	private function getCountersEmptyState(): array
	{
		return [
			CounterDictionary::META_PROP_ALL => [],
			CounterDictionary::META_PROP_NEW_EVENTS => [],
			CounterDictionary::META_PROP_OPEN_EVENTS => [
				'category' => [],
				'total' => 0,
			],
		];
	}
}
