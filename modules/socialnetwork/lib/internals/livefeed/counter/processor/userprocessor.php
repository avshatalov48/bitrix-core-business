<?php

namespace Bitrix\Socialnetwork\Internals\LiveFeed\Counter\Processor;

use Bitrix\Socialnetwork\Internals\LiveFeed\Counter\Exception\UnknownCounterException;
use Bitrix\Socialnetwork\Internals\LiveFeed\Counter;
use Bitrix\Socialnetwork\Internals\LiveFeed\Counter\Queue\Queue;
use Bitrix\Socialnetwork\Internals\LiveFeed\Counter\Queue\Agent;
use Bitrix\Socialnetwork\Internals\LiveFeed\Counter\Collector;
use Bitrix\Socialnetwork\Internals\LiveFeed\Counter\CounterDictionary;
use Bitrix\Socialnetwork\Internals\LiveFeed\Counter\CounterController;
use Bitrix\Socialnetwork\Item\Log;
use Bitrix\Socialnetwork\UserContentViewTable;

class UserProcessor
{
	use CommandTrait;

	private int $userId;
	private ?Collector\UserCollector $userCollector = null;
	private ?Collector\SonetLogCollector $sonetLogCollector = null;

	private array|null $userSonetLogs = null;

	private static array $instances = [];

	public static function getInstance(int $userId)
	{
		if (!array_key_exists($userId, self::$instances))
		{
			self::$instances[$userId] = new self($userId);
		}

		return self::$instances[$userId];
	}

	private function __construct(int $userId)
	{
		$this->userId = $userId;
	}

	public function readAll(int $groupId = 0): void
	{
		$types = [
			CounterDictionary::COUNTER_NEW_POSTS,
			CounterDictionary::COUNTER_NEW_COMMENTS,
		];

		$itemsToReset = $this->getSonetLogItemsByGroup($groupId);

		self::reset($this->userId, $types, $itemsToReset);
		Counter\State\Factory::reloadState($this->userId);
	}

	public function recountAll(string $counter): void
	{
		if (!in_array($counter, [CounterDictionary::COUNTER_NEW_POSTS]))
		{
			throw new UnknownCounterException();
		}

		$total = $this->getSonetLogCollector()->fetchTotal();
		$limit = CounterController::STEP_LIMIT;

		foreach ((new Collector\Paginator($total, $limit)) as $offset)
		{
			$rows = $this->getSonetLogCollector()->fetch($limit, $offset);
			$this->addToQueue($counter, $rows);
		}

		(new Agent())->addAgent();
	}

	public function add(string $counter, array $logIds = []): void
	{
		if (!in_array($counter, [CounterDictionary::COUNTER_NEW_POSTS, CounterDictionary::COUNTER_NEW_COMMENTS]))
		{
			throw new UnknownCounterException();
		}

		$counters = $this->getUserCollector()->add($counter, $logIds);

		$counterTypes = [$counter];
		self::reset($this->userId, $counterTypes, $logIds);
		$this->batchInsert($counters);

		Counter\State\Factory::getState($this->userId)->updateState($counters, $counterTypes, $logIds);
	}

	public function recount(string $counter, array $logIds = []): void
	{
		if (!in_array($counter, [CounterDictionary::COUNTER_NEW_POSTS, CounterDictionary::COUNTER_NEW_COMMENTS]))
		{
			throw new UnknownCounterException();
		}

		$counters = $this->getUserCollector()->recount($counter, $logIds);

		$counterTypes = [$counter];
		self::reset($this->userId, $counterTypes, $logIds);
		$this->batchInsert($counters);

		Counter\State\Factory::getState($this->userId)->updateState($counters, $counterTypes, $logIds);
	}

	public function seen(string $counter, array $logIds = []): void
	{
		if (!in_array($counter, [CounterDictionary::COUNTER_NEW_POSTS, CounterDictionary::COUNTER_NEW_COMMENTS]))
		{
			throw new UnknownCounterException();
		}

		$counters = [];
		$counterTypes = [$counter];
		self::reset($this->userId, $counterTypes, $logIds);
		$this->batchInsert($counters);

		Counter\State\Factory::getState($this->userId)->updateState($counters, $counterTypes, $logIds);
	}

	private function addToQueue(string $counter, array $logIds): void
	{
		Queue::getInstance()->add($this->userId, $counter, $logIds);
	}

	private function getUserCollector(): Collector\UserCollector
	{
		if (!$this->userCollector)
		{
			$this->userCollector = Collector\UserCollector::getInstance($this->userId);
		}
		return $this->userCollector;
	}

	private function getSonetLogCollector(): Collector\SonetLogCollector
	{
		if (!$this->sonetLogCollector)
		{
			$this->sonetLogCollector = Collector\SonetLogCollector::getInstance($this->userId);
		}
		return $this->sonetLogCollector;
	}

	private function getSonetLogItemsByGroup(int $groupId): array
	{
		$items = [];

		foreach (Counter\State\Factory::getState($this->userId) as $item)
		{
			if ($item['GROUP_ID'] == $groupId)
			{
				$items[] = $item['SONET_LOG_ID'];
			}
		}

		return $items;
	}
}