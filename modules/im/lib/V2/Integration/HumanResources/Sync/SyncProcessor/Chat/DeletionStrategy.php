<?php

namespace Bitrix\Im\V2\Integration\HumanResources\Sync\SyncProcessor\Chat;

use Bitrix\HumanResources\Item\Node;
use Bitrix\HumanResources\Type\RelationEntityType;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\ChannelChat;
use Bitrix\Im\V2\Relation\DeleteUserConfig;
use Bitrix\Im\V2\Relation\Reason;

class DeletionStrategy extends Strategy
{
	public function processUsers(Chat $chat, array $userIds): void
	{
		$this->deleteUsers($chat, $userIds);
	}

	protected function deleteUsers(Chat $chat, array $userIds): void
	{
		$userIds = $this->excludeManuallyAddedUsers($chat, $userIds);
		$userIds = $this->excludeUsersWithOtherRelations($chat, $userIds);
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

	protected function excludeManuallyAddedUsers(Chat $chat, array $userIds): array
	{
		$filteredUsers = [];
		$relations = $chat->getRelations();
		foreach ($userIds as $userId)
		{
			$relation = $relations->getByUserId($userId, $chat->getId());
			if ($relation !== null && $relation->getReason() !== Reason::DEFAULT)
			{
				$filteredUsers[] = $userId;
			}
		}

		return $filteredUsers;
	}

	public function getFinishMessageCode(Chat $chat, ?Node $node): ?string
	{
		if ($chat instanceof ChannelChat)
		{
			return match ($node->isTeam())
			{
				true => 'IM_HR_INTEGRATION_CHANNEL_FINISH_DELETE_TEAM',
				false => 'IM_HR_INTEGRATION_CHANNEL_FINISH_DELETE_MSGVER_1',
			};
		}

		return match ($node->isTeam())
		{
			true => 'IM_HR_INTEGRATION_CHAT_FINISH_DELETE_TEAM',
			false => 'IM_HR_INTEGRATION_CHAT_FINISH_DELETE_MSGVER_2',
		};
	}
}
