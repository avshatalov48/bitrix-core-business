<?php

namespace Bitrix\Socialnetwork\Internals\LiveFeed\Counter\Processor;

use Bitrix\Socialnetwork\Internals\EventService\EventDictionary;
use Bitrix\Socialnetwork\Internals\EventService\Service;
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

		$groupsToReset = $groupId === 0
			? []
			: [$groupId];

		$state = Counter\State\Factory::getState($this->userId);
		// mark everything as 'viewed'
		foreach ($state  as $row)
		{
			$stateGroupId = (int)$row['GROUP_ID'];
			$stateLogId = (int)$row['SONET_LOG_ID'];

			if ($stateGroupId !== $groupId || $stateLogId === 0)
			{
				continue;
			}

			$logItem = Log::getById($row['SONET_LOG_ID']);
			if ($logItem === false)
			{
				continue;
			}

			$typeId = $logItem->getFields()['RATING_TYPE_ID'] ?? null;
			$entityId = $logItem->getFields()['RATING_ENTITY_ID'] ?? null;

			if (!$typeId || !$entityId)
			{
				continue;
			}

			UserContentViewTable::set([
				'userId' => $this->userId,
				'typeId' => $typeId,
				'entityId' => $entityId,
				'logId' => $row['SONET_LOG_ID'],
				'save' => true
			]);
		}

		self::reset($this->userId, $types, [], $groupsToReset);
		Counter\State\Factory::reloadState($this->userId);
		Service::addEvent(EventDictionary::EVENT_SPACE_LIVEFEED_COUNTER_UPD,[
			'USER_ID' => $this->userId,
		]);
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
		Service::addEvent(EventDictionary::EVENT_SPACE_LIVEFEED_COUNTER_UPD,[
			'USER_ID' => $this->userId,
		]);
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
}