<?php

namespace Bitrix\Socialnetwork\Internals\LiveFeed\Counter;

use Bitrix\Socialnetwork\Internals\LiveFeed\Counter;
use Bitrix\Socialnetwork\Internals\Registry\UserRegistry;

abstract class CounterState implements \Iterator
{
	private Counter\Loader $loader;

	protected int $userId;
	protected array $counters = [];

	protected function __construct(int $userId, Counter\Loader $loader)
	{
		$this->userId = $userId;
		$this->loader = $loader;
		$this->init();
	}

	abstract public function rewind(): void;

	#[\ReturnTypeWillChange]
	abstract public function current();

	#[\ReturnTypeWillChange]
	abstract public function key();

	abstract public function next(): void;

	abstract public function valid(): bool;

	abstract public function getSize(): int;

	abstract protected function loadCounters(): void;

	abstract public function updateState(array $rawCounters, array $types = [], array $sonetLogIds = []): void;

	public function init(): void
	{
		$this->counters = $this->getCountersEmptyState();
		$this->loadCounters();
	}

	public function getLoader(): Counter\Loader
	{
		return $this->loader;
	}

	public function isCounted(): bool
	{
		return $this->loader->isCounted();
	}

	public function getClearedDate(): int
	{
		return $this->loader->getClearedDate();
	}

	public function resetCache(): void
	{
		$this->loader->resetCache();
	}

	public function getRawCounters(string $meta = CounterDictionary::META_PROP_ALL): array
	{
		return $this->counters[$meta] ?? [];
	}

	public function getValue(string $name, int $groupId = null): int
	{
		$counters = $this->counters[CounterDictionary::META_PROP_ALL];

		if ($groupId >= 0)
		{
			if (
				!array_key_exists($name, $counters)
				|| !array_key_exists($groupId, $counters[$name])
			)
			{
				return 0;
			}

			return $counters[$name][$groupId];
		}

		return 0;

//		if (!array_key_exists($name, $counters))
//		{
//			return 0;
//		}
//
//		return array_sum($counters[$name]);
	}

	/**
	 * Updates counters based on current state
	 * @return void
	 */
	protected function updateRawCounters(): void
	{
		$this->counters = $this->getCountersEmptyState();

		$user = UserRegistry::getInstance($this->userId);
		$groups = $user->getUserGroups(UserRegistry::MODE_GROUP);

		$tmpHeap[] = [];
		foreach ($this as $item)
		{
			if ($this->getLoader()->isCounterFlag($item['TYPE']))
			{
				continue;
			}

			$logId = $item['SONET_LOG_ID'];
			$groupId = $item['GROUP_ID'];
			$value = $item['VALUE'];
			$type = $item['TYPE'];

			$meta = $this->getMetaProp($item, $groups);
			$subType = $this->getItemSubType($type);

			if (!isset($this->counters[$meta][$type][$groupId]))
			{
				$this->counters[$meta][$type][$groupId] = 0;
			}
			if (!isset($this->counters[$meta][$subType][$groupId]))
			{
				$this->counters[$meta][$subType][$groupId] = 0;
			}
			if (!isset($tmpHeap[$meta][$subType][$groupId]))
			{
				$tmpHeap[$meta][$subType][$groupId] = [];
			}
			if (!isset($this->counters[CounterDictionary::META_PROP_SONET][$type][$groupId]))
			{
				$this->counters[CounterDictionary::META_PROP_SONET][$type][$groupId] = 0;
			}
			if (!isset($this->counters[CounterDictionary::META_PROP_SONET][$subType][$groupId]))
			{
				$this->counters[CounterDictionary::META_PROP_SONET][$subType][$groupId] = 0;
			}
			if (!isset($this->counters[CounterDictionary::META_PROP_ALL][$type][$groupId]))
			{
				$this->counters[CounterDictionary::META_PROP_ALL][$type][$groupId] = 0;
			}
			if (!isset($this->counters[CounterDictionary::META_PROP_ALL][$subType][$groupId]))
			{
				$this->counters[CounterDictionary::META_PROP_ALL][$subType][$groupId] = 0;
			}

			if (!isset($tmpHeap[$meta][$type][$groupId][$logId]))
			{
				$tmpHeap[$meta][$type][$groupId][$logId] = $value;
				$this->counters[$meta][$type][$groupId] += $value;

				if (in_array($meta, [CounterDictionary::META_PROP_GROUP]))
				{
					$this->counters[CounterDictionary::META_PROP_SONET][$type][$groupId] += $value;
				}
			}

			if (
				$type !== $subType
				&& !isset($tmpHeap[$meta][$subType][$groupId][$logId])
			)
			{
				$tmpHeap[$meta][$subType][$groupId][$logId] = $value;
				$this->counters[$meta][$subType][$groupId] += $value;

				if (in_array($meta, [CounterDictionary::META_PROP_GROUP]))
				{
					$this->counters[CounterDictionary::META_PROP_SONET][$subType][$groupId] += $value;
				}
			}

			if (!isset($tmpHeap[CounterDictionary::META_PROP_ALL][$type][$groupId][$logId]))
			{
				$tmpHeap[CounterDictionary::META_PROP_ALL][$type][$groupId][$logId] = $value;
				$this->counters[CounterDictionary::META_PROP_ALL][$type][$groupId] += $value;
			}

			if (
				$type !== $subType
				&& !isset($tmpHeap[CounterDictionary::META_PROP_ALL][$subType][$groupId][$logId])
			)
			{
				$tmpHeap[CounterDictionary::META_PROP_ALL][$subType][$groupId][$logId] = $value;
				$this->counters[CounterDictionary::META_PROP_ALL][$subType][$groupId] += $value;
			}
		}

		unset($tmpHeap);
	}

	private function getCountersEmptyState(): array
	{
		return [
			CounterDictionary::META_PROP_ALL => [],
			CounterDictionary::META_PROP_GROUP => [],
			CounterDictionary::META_PROP_NONE => [],
		];
	}

	private function getItemSubType(string $type): string
	{
		if (in_array($type, [CounterDictionary::COUNTER_NEW_COMMENTS]))
		{
			return CounterDictionary::COUNTER_NEW_COMMENTS;
		}

		if (in_array($type, [CounterDictionary::COUNTER_NEW_POSTS]))
		{
			return CounterDictionary::COUNTER_NEW_POSTS;
		}

		return $type;
	}

	private function getMetaProp(array $item, array $groups): string
	{
		if (array_key_exists($item['GROUP_ID'], $groups))
		{
			return CounterDictionary::META_PROP_GROUP;
		}

		return CounterDictionary::META_PROP_NONE;
	}
}