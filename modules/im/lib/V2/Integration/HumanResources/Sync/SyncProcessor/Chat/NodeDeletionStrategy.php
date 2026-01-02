<?php

namespace Bitrix\Im\V2\Integration\HumanResources\Sync\SyncProcessor\Chat;

use Bitrix\HumanResources\Item\Node;
use Bitrix\HumanResources\Type\RelationEntityType;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Integration\HumanResources\Sync\Item\QueueItem;
use Bitrix\Im\V2\Relation\DeleteUserConfig;
use Bitrix\Im\V2\Relation\Reason;

class NodeDeletionStrategy extends Strategy
{
	protected int $skippedUsersCount = 0;

	public function getUserIds(QueueItem $item, int $limit): array
	{
		$chat = Chat::getInstance($item->syncInfo->entityId);
		$relations = $chat->getRelationFacade()->getByReason(Reason::STRUCTURE);

		$userIds = $relations->getUserIds();
		sort($userIds);

		return array_slice($userIds, $item->pointer, $limit);
	}

	public function processUsers(Chat $chat, array $userIds): void
	{
		$this->deleteUsers($chat, $userIds);
	}

	protected function deleteUsers(Chat $chat, array $userIds): void
	{
		$usersCount = count($userIds);
		$userIds = $this->excludeUsersWithOtherRelations($chat, $userIds);
		$this->skippedUsersCount = $usersCount - count($userIds);

		foreach ($userIds as $userId)
		{
			if ($chat->getAuthorId() === (int)$userId)
			{
				$chat->getRelationByUserId((int)$userId)?->setReason(Reason::DEFAULT)?->save();

				continue;
			}

			$config = new DeleteUserConfig(false, false, false, true);
			$chat->deleteUser($userId, $config);
		}
	}

	protected function excludeUsersWithOtherRelations(Chat $chat, array $userIds): array
	{
		return $this->relationService->getUsersNotInRelation(RelationEntityType::CHAT, $chat->getId(), $userIds);
	}

	public function getPointer(int $pointer, int $limit): int
	{
		return $pointer + $this->skippedUsersCount;
	}

	public function getFinishMessageCode(Chat $chat, ?Node $node): ?string
	{
		return null;
	}
}
