<?php

namespace Bitrix\Im\V2\Integration\HumanResources\Sync\SyncProcessor;

use Bitrix\HumanResources\Type\RelationEntityType;
use Bitrix\Im\Recent;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Integration\HumanResources\Sync\Item\EntityType;
use Bitrix\Im\V2\Integration\HumanResources\Sync\Item\QueueItem;
use Bitrix\Im\V2\Integration\HumanResources\Sync\Item\Status;
use Bitrix\Im\V2\Integration\HumanResources\Sync\Item\SyncDirection;
use Bitrix\Im\V2\Integration\HumanResources\Sync\Item\SyncInfo;
use Bitrix\Im\V2\Integration\HumanResources\Sync\Result\IterationResult;
use Bitrix\Im\V2\Integration\HumanResources\Sync\Result\QueueItemResult;
use Bitrix\Im\V2\Relation;
use Bitrix\Im\V2\Relation\AddUsersConfig;
use Bitrix\Im\V2\Relation\Reason;
use Bitrix\Main\Config\Option;

class User extends Base
{
	protected const DEFAULT_LIMIT = 20;
	protected const LIMIT_OPTION_NAME = 'hr_sync_chat_limit';

	protected static int $countOfProcessedChats = 0;

	public function makeIteration(QueueItem $item): IterationResult
	{
		$result = new IterationResult();

		if (self::$countOfProcessedChats >= self::getLimit())
		{
			return $result;
		}

		$chatIds = $this->getChatIds($item);
		if (count($chatIds) < self::getLimit())
		{
			$result->setHasMore(false);
		}

		self::$countOfProcessedChats += count($chatIds);

		match ($item->syncInfo->direction)
		{
			SyncDirection::ADD => $this->addToChats($chatIds, $item),
			SyncDirection::DELETE => $this->deleteFromChats($chatIds, $item),
		};
		$item->updatePointer($item->pointer + self::getLimit());

		return $result;
	}

	public function getOrCreateWithLock(SyncInfo $syncInfo): QueueItemResult
	{
		$result = parent::getOrCreateWithLock($syncInfo);

		$item = $result->getResult();
		if ($item && $item->status === Status::NEW)
		{
			$item->unlock();
			$result->setSkip(true);
		}

		return $result;
	}

	protected static function getLimit(): int
	{
		return Option::get('im', self::LIMIT_OPTION_NAME, self::DEFAULT_LIMIT);
	}

	protected function getEntityType(): EntityType
	{
		return EntityType::USER;
	}

	protected function getChatIds(QueueItem $item): array
	{
		$chatIds = [];

		$relations = $this->relationRepository->findRelationsByNodeIdAndRelationType(
			$item->syncInfo->nodeId,
			RelationEntityType::CHAT,
			self::getLimit(),
			$item->pointer
		);

		foreach ($relations as $relation)
		{
			$chatIds[] = $relation->entityId;
		}

		return $chatIds;
	}

	protected function addToChats(array $chatIds, QueueItem $item): void
	{
		foreach ($chatIds as $chatId)
		{
			$this->addToChat(Chat::getInstance($chatId), $item);
		}
	}

	protected function addToChat(Chat $chat, QueueItem $item): void
	{
		if ($chat instanceof Chat\NullChat)
		{
			return;
		}

		$userId = $item->syncInfo->entityId;
		$chat = $chat->withContextUser(0);
		$chat->addUsers([$userId], new AddUsersConfig(hideHistory: false, withMessage: false, reason: Reason::STRUCTURE));
		$relations = $chat->getRelations()->filter(fn (Relation $relation) => $relation->getUserId() === $userId);
		Recent::raiseChat($chat, $relations);
	}

	protected function deleteFromChats(array $chatIds, QueueItem $item): void
	{
		foreach ($chatIds as $chatId)
		{
			$this->deleteFromChat(Chat::getInstance($chatId), $item);
		}
	}

	protected function deleteFromChat(Chat $chat, QueueItem $item): void
	{
		if ($chat instanceof Chat\NullChat)
		{
			return;
		}

		$userId = $item->syncInfo->entityId;
		if ($this->isUserInOtherRelations($chat, $userId) || $this->isUserAddedManually($chat, $userId))
		{
			return;
		}

		if ($this->isUserChatOwner($chat, $userId))
		{
			$chat->getRelationByUserId($userId)?->setReason(Reason::DEFAULT)->save();

			return;
		}

		$config = New Relation\DeleteUserConfig(false, false, false, true);
		$chat
			->withContextUser(0)
			->deleteUser($userId,$config)
		;
	}

	protected function isUserInOtherRelations(Chat $chat, int $userId): bool
	{
		$users = $this->relationService->getUsersNotInRelation(RelationEntityType::CHAT, $chat->getId(), [$userId]);

		return empty($users);
	}

	protected function isUserAddedManually(Chat $chat, int $userId): bool
	{
		return $chat->getRelations()->getByUserId($userId, $chat->getId())?->getReason() === Reason::DEFAULT;
	}

	protected function isUserChatOwner(Chat $chat, int $userId): bool
	{
		return $chat->getAuthorId() === $userId;
	}
}
