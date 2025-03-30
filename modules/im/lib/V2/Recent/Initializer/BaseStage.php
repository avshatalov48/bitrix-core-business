<?php

namespace Bitrix\Im\V2\Recent\Initializer;

use Bitrix\Im\V2\Recent\Initializer\Queue\QueueItem;
use Bitrix\Im\V2\Recent\Initializer\Stage\OtherUsersStage;
use Bitrix\Im\V2\Recent\Initializer\Stage\TargetUserStage;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;

abstract class BaseStage implements Stage
{
	protected int $targetId;
	protected DateTime $currentDate;
	protected int $gapTime = self::WITHOUT_GAP_TIME;

	public function __construct(int $targetId)
	{
		$this->targetId = $targetId;
	}

	public static function getInstance(StageType $type, int $targetId): Stage
	{
		return match ($type)
		{
			StageType::Target => new TargetUserStage($targetId),
			StageType::Other => new OtherUsersStage($targetId),
		};
	}

	public static function createFromQueueItem(QueueItem $queueItem): Stage
	{
		return static::getInstance($queueItem->stageType, $queueItem->userId);
	}

	public function setGapTime(int $gapTime = self::GAP_TIME): static
	{
		$this->gapTime = $gapTime;

		return $this;
	}

	final public function getItems(InitialiazerResult $result): InitialiazerResult
	{
		$users = $result->getItems();
		$filteredUsers = $this->filterUsers($users);
		$items = $this->getItemsByUsers($filteredUsers);

		return $result->setItems($items)->setHasNextStep($this->hasNextStep($result));
	}

	public function sendPullAfterInsert(array $items): void
	{
		if (empty($items) || !Loader::includeModule('pull'))
		{
			return;
		}

		$recipients = $this->getPullRecipients($items);
		$pullParams = $this->getPullParams($items);
		\Bitrix\Pull\Event::add($recipients, [
			'module_id' => 'im',
			'command' => 'userShowInRecent',
			'expiry' => 3600,
			'params' => $pullParams,
			'extra' => \Bitrix\Im\Common::getPullExtra()
		]);
	}

	abstract protected function getPullRecipients(array $items): array;

	abstract protected function getPullParams(array $items): array;

	final protected function filterUsers(array $users): array
	{
		$usersWithExistingItems = $this->getUsersWithExistingItems($users);

		return array_diff_key($users, $usersWithExistingItems);
	}

	abstract protected function getUsersWithExistingItems(array $users): array;

	protected function getItemsByUsers(array $users): array
	{
		$result = [];

		foreach ($users as $user)
		{
			if ($user === $this->targetId)
			{
				continue;
			}
			$result[] = $this->getItemByTargetAndUser($this->targetId, $user);
		}

		return $result;
	}

	protected function getCurrentDate(): DateTime
	{
		// We need to subtract n seconds from the current time to create a time gap.
		// This ensures that the order of elements in the "recent" is correct.
		// Collabers should always appear below the main collab chat.
		if ($this->gapTime)
		{
			$this->currentDate ??= (new DateTime())->add("-{$this->gapTime} seconds");
		}
		else
		{
			$this->currentDate ??= new DateTime();
		}

		return $this->currentDate;
	}

	abstract protected function getItemByTargetAndUser(int $targetUserId, int $otherUserId): array;

	protected function getItem(int $userId, int $itemId): array
	{
		return [
			'USER_ID' => $userId,
			'ITEM_TYPE' => 'P',
			'ITEM_ID' => $itemId,
			'DATE_MESSAGE' => $this->getCurrentDate(),
			'DATE_UPDATE' => $this->getCurrentDate(),
			'DATE_LAST_ACTIVITY' => $this->getCurrentDate(),
		];
	}

	protected function hasNextStep(InitialiazerResult $result): bool
	{
		return $result->hasNextStep();
	}
}
