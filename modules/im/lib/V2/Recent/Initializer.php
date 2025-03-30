<?php

namespace Bitrix\Im\V2\Recent;

use Bitrix\Im\Model\RecentTable;
use Bitrix\Im\V2\Common\PeriodAgentTrait;
use Bitrix\Im\V2\Entity\User\User;
use Bitrix\Im\V2\Entity\User\UserCollection;
use Bitrix\Im\V2\Logger;
use Bitrix\Im\V2\Recent\Initializer\BaseSource;
use Bitrix\Im\V2\Recent\Initializer\InitialiazerResult;
use Bitrix\Im\V2\Recent\Initializer\Queue\DequeueResult;
use Bitrix\Im\V2\Recent\Initializer\Queue\QueueItem;
use Bitrix\Im\V2\Recent\Initializer\QueueService;
use Bitrix\Im\V2\Recent\Initializer\Source;
use Bitrix\Im\V2\Recent\Initializer\SourceType;
use Bitrix\Im\V2\Result;

class Initializer
{
	use PeriodAgentTrait;

	protected const AGENT_SHORT_PERIOD = 5;
	protected const AGENT_LONG_PERIOD = 300;
	protected const SELECTED_ITEM_LIMIT = 50;
	protected const INSERTED_ITEM_LIMIT = 50;
	private const INSERT_PARAMS = ['DEADLOCK_SAFE' => true, 'UNIQUE_FIELDS' => ['USER_ID', 'ITEM_TYPE', 'ITEM_ID']];

	protected static int $selectedItemLimitCounter = 0;
	protected static int $insertedItemLimitCounter = 0;
	protected static array $instances = [];

	protected int $targetId;
	protected Source $source;

	protected function __construct(int $targetId, Source $source)
	{
		$this->targetId = $targetId;
		$this->source = $source;
	}

	protected static function getInstance(QueueItem $queueItem): static
	{
		$instanceKey = "{$queueItem->userId}_{$queueItem->stageType->value}_{$queueItem->sourceType->value}_{$queueItem->sourceId}";
		if (isset(self::$instances[$instanceKey]))
		{
			return self::$instances[$instanceKey];
		}

		$source = BaseSource::createFromQueueItem($queueItem);

		self::$instances[$instanceKey] = new static($queueItem->userId, $source);

		return self::$instances[$instanceKey];
	}

	public static function onAfterUsersAddToCollab(array $users, int $chatCollabId): Result
	{
		return self::addMulti($users, SourceType::Collab, $chatCollabId);
	}

	public static function onAfterUserAcceptInvite(int $userId): Result
	{
		return self::add($userId, SourceType::Collabs, null, true);
	}

	public static function executeAgent(): string
	{
		while (!self::isLimitsExceeded())
		{
			$result = self::executeAgentIteration();

			if (!$result->hasMore())
			{
				break;
			}

			$queueItem = $result->getQueueItem();

			QueueService::getInstance()->save($queueItem);
		}

		self::calculateAndSetPeriod(true);

		return self::getAgentName();
	}

	protected static function executeAgentIteration(): DequeueResult
	{
		$dequeueResult = new DequeueResult();

		$queueItem = QueueService::getInstance()->getFirst();
		if (!$queueItem)
		{
			return $dequeueResult->setHasMore(false);
		}

		try
		{
			$result = static::doSteps($queueItem);
			$queueItem = $result->getQueueItem();

			return $dequeueResult->setQueueItem($queueItem?->unlock());
		}
		catch (\Throwable $exception)
		{
			(new Logger('imRecentInitializer'))->logThrowable($exception);

			return $dequeueResult->setQueueItem($queueItem->unlock());
		}
	}

	protected static function doSteps(QueueItem $queueItem): InitialiazerResult
	{
		$result = (new InitialiazerResult())->setQueueItem($queueItem);

		while (!self::isLimitsExceeded())
		{
			if (!$queueItem)
			{
				break;
			}

			$initializer = static::getInstance($queueItem);
			$result = $initializer->doStep($queueItem);
			$queueItem = $result->getQueueItem();
		}

		return $result;
	}

	protected function doStep(QueueItem $queueItem): InitialiazerResult
	{
		$result = $this->source->getItems($queueItem->pointer, self::SELECTED_ITEM_LIMIT);
		if (!$result->isSuccess())
		{
			return $result;
		}

		$items = $result->getItems();
		$this->insert($items);
		$this->source->getStage()->sendPullAfterInsert($items);
		$queueItem = $this->updateQueueItem($queueItem, $result);
		$this->incrementLimitCounters($result);

		return $result->setQueueItem($queueItem);
	}

	protected static function addMulti(array $users, SourceType $sourceType, ?int $sourceId = null): Result
	{
		$users = self::filterInvitedUsers($users);
		$lastQueueItem = null;
		$queueItems = [];
		foreach ($users as $user)
		{
			if (!self::isLimitsExceeded())
			{
				$firstStep = QueueItem::createFirstStep($user, $sourceType, $sourceId);
				$result = static::doSteps($firstStep);
				$lastQueueItem = $result->getQueueItem();
			}
			else
			{
				$queueItems[] = QueueItem::createFirstStep($user, $sourceType, $sourceId);
			}
		}

		if ($lastQueueItem)
		{
			array_unshift($queueItems, $lastQueueItem);
		}

		if (!empty($queueItems))
		{
			QueueService::getInstance()->addMulti($queueItems);
		}

		self::calculateAndSetPeriod(false);

		return new Result();
	}

	protected static function add(int $targetId, SourceType $sourceType, ?int $sourceId = null, bool $isFirstInit = false): Result
	{
		if (self::isUserInvited($targetId))
		{
			return new Result();
		}

		$firstStep = QueueItem::createFirstStep($targetId, $sourceType, $sourceId, $isFirstInit);
		$result = static::doSteps($firstStep);
		$queueItem = $result->getQueueItem();
		$queueItem = QueueService::getInstance()->save($queueItem);
		self::calculateAndSetPeriod(false);

		return $result->setQueueItem($queueItem);
	}

	protected function updateQueueItem(QueueItem $queueItem, InitialiazerResult $result): ?QueueItem
	{
		if (!$result->isSuccess())
		{
			return $queueItem->setErrorStatus();
		}

		if (!$result->hasNextStep())
		{
			$nextStage = $queueItem->stageType->getNext();
			if ($nextStage === null)
			{
				return $this->finalize($queueItem);
			}

			return $queueItem->updatePointer('', $nextStage);
		}

		return $queueItem->updatePointer($result->getNextPointer());
	}

	protected function finalize(QueueItem $queueItem): ?QueueItem
	{
		QueueService::getInstance()->delete($queueItem);

		return null;
	}

	protected function incrementLimitCounters(InitialiazerResult $result): void
	{
		self::$insertedItemLimitCounter += count($result->getItems());
		self::$selectedItemLimitCounter += $result->getSelectedItemsCount();
	}

	protected static function isLimitsExceeded(): bool
	{
		return
			self::$selectedItemLimitCounter >= self::SELECTED_ITEM_LIMIT
			|| self::$insertedItemLimitCounter >= self::INSERTED_ITEM_LIMIT
		;
	}

	protected static function filterInvitedUsers(array $users): array
	{
		$userCollection = new UserCollection($users);
		$userCollection->fillOnlineData();

		return array_filter($users, static fn (int $userId) => !self::isUserInvited($userId));
	}

	protected static function isUserInvited(int $userId): bool
	{
		return User::getInstance($userId)->getLastActivityDate() === null;
	}

	protected function insert(array $fields): void
	{
		RecentTable::multiplyInsertWithoutDuplicate($fields, self::INSERT_PARAMS);
	}

	protected static function getAgentName(): string
	{
		return '\Bitrix\Im\V2\Recent\Initializer::executeAgent();';
	}

	protected static function calculateAndSetPeriod(bool $fromAgent): void
	{
		self::setPeriodByName($fromAgent, self::getAgentName(), self::getNewAgentPeriodGetter());
	}

	protected static function getNewAgentPeriodGetter(): callable
	{
		return static fn () => QueueService::getInstance()->isQueueEmpty() ? self::AGENT_LONG_PERIOD : self::AGENT_SHORT_PERIOD;
	}

	protected static function isAgentPeriodShort(int $newPeriod): bool
	{
		return $newPeriod === self::AGENT_SHORT_PERIOD;
	}
}
