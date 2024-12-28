<?php

namespace Bitrix\Im\V2\Recent\Initializer;

use Bitrix\Im\V2\Recent\Initializer\Stage\OtherUsersStage;
use Bitrix\Im\V2\Recent\Initializer\Stage\TargetUserStage;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;

abstract class BaseStage implements Stage
{
	protected Source $source;
	protected int $targetId;

	public function __construct(Source $source, int $targetId)
	{
		$this->source = $source;
		$this->targetId = $targetId;
	}

	public static function getInstance(StageType $type, Source $source, int $targetId): Stage
	{
		return match ($type)
		{
			StageType::Target => new TargetUserStage($source, $targetId),
			StageType::Other => new OtherUsersStage($source, $targetId),
		};
	}

	public function getSource(): Source
	{
		return $this->source;
	}

	final public function getItems(string $pointer, int $limit): InitialiazerResult
	{
		$result = $this->getUsers($pointer, $limit);
		$users = $result->getItems();
		$filteredUsers = $this->filterUsers($users);
		$items = $this->getItemsByUsers($filteredUsers);

		return $result->setItems($items)->setHasNextStep($this->hasNextStep($result));
	}

	public function sendPullAfterInsert(array $items): void
	{
		if (!Loader::includeModule('pull'))
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

	protected function getUsers(string $pointer, int $limit): InitialiazerResult
	{
		return $this->source->getUsers($pointer, $limit);
	}

	final protected function filterUsers(array $users): array
	{
		$usersWithExistingItems = $this->getUsersWithExistingItems($users);

		return array_diff_key($users, $usersWithExistingItems);
	}

	abstract protected function getUsersWithExistingItems(array $users): array;

	protected function getItemsByUsers(array $users): array
	{
		$result = [];
		$currentDate = new DateTime();

		foreach ($users as $user)
		{
			if ($user === $this->targetId)
			{
				continue;
			}
			$result[] = $this->getItemByTargetAndUser($this->targetId, $user, $currentDate);
		}

		return $result;
	}

	abstract protected function getItemByTargetAndUser(int $targetUserId, int $otherUserId, DateTime $date): array;

	protected function getItem(int $userId, int $itemId, DateTime $date): array
	{
		return [
			'USER_ID' => $userId,
			'ITEM_TYPE' => 'P',
			'ITEM_ID' => $itemId,
			'DATE_MESSAGE' => $date,
			'DATE_UPDATE' => $date,
			'DATE_LAST_ACTIVITY' => $date,
		];
	}

	protected function hasNextStep(InitialiazerResult $result): bool
	{
		return $result->hasNextStep();
	}
}
