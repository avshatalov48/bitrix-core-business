<?php

namespace Bitrix\Im\V2\Integration\HumanResources\Sync\SyncProcessor;

use Bitrix\HumanResources\Type\RelationEntityType;
use Bitrix\Im\V2\Chat\OpenChannelChat;
use Bitrix\Im\V2\Integration\HumanResources\Sync\Item\EntityType;
use Bitrix\Im\V2\Integration\HumanResources\Sync\Item\QueueItem;
use Bitrix\Im\V2\Integration\HumanResources\Sync\Item\SyncDirection;
use Bitrix\Im\V2\Integration\HumanResources\Sync\Result\IterationResult;
use Bitrix\Im\V2\Relation\AddUsersConfig;
use Bitrix\Im\V2\Relation\DeleteUserConfig;
use Bitrix\Im\V2\Relation\Reason;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class Chat extends Base
{
	protected const DEFAULT_LIMIT = 200;
	protected const LIMIT_OPTION_NAME = 'hr_sync_user_limit';

	protected static int $countOfAddedUsers = 0;

	public function makeIteration(QueueItem $item): IterationResult
	{
		$result = new IterationResult();

		if (!Loader::includeModule('humanresources'))
		{
			return $result;
		}

		if (self::$countOfAddedUsers >= self::getLimit())
		{
			return $result;
		}

		$chat = \Bitrix\Im\V2\Chat::getInstance($item->syncInfo->entityId);

		if ($chat instanceof \Bitrix\Im\V2\Chat\NullChat)
		{
			return $result->setHasMore(false);
		}

		$chat = $chat->withContextUser(0);
		$users = $this->getUsers($item);
		self::$countOfAddedUsers += count($users);
		match ($item->syncInfo->direction)
		{
			SyncDirection::ADD => $this->addUsers($chat, $users),
			SyncDirection::DELETE => $this->deleteUsers($chat, $users),
		};
		$item->updatePointer($item->pointer + self::getLimit());

		return $result->setHasMore(count($users) === self::getLimit());
	}

	protected static function getLimit(): int
	{
		return Option::get('im', self::LIMIT_OPTION_NAME, self::DEFAULT_LIMIT);
	}

	protected function addUsers(\Bitrix\Im\V2\Chat $chat, array $users): void
	{
		$chat->addUsers($users, new AddUsersConfig(hideHistory: false, withMessage: false, reason: Reason::STRUCTURE));
		$this->deduplicateUsers($chat);
	}

	protected function deduplicateUsers(\Bitrix\Im\V2\Chat $chat): void
	{
		//
	}

	protected function deleteUsers(\Bitrix\Im\V2\Chat $chat, array $users): void
	{
		$users = $this->filterManualAddedUsers($chat, $users);
		$users = $this->filterUsersInOtherRelations($chat, $users);
		foreach ($users as $user)
		{
			if ($chat->getAuthorId() === (int)$user)
			{
				$chat->getRelationByUserId((int)$user)?->setReason(Reason::DEFAULT)?->save();

				continue;
			}

			$config = new DeleteUserConfig(false, false, false, true);
			$chat->deleteUser($user, $config);
		}
	}

	protected function filterUsersInOtherRelations(\Bitrix\Im\V2\Chat $chat, array $users): array
	{
		return $this->relationService->getUsersNotInRelation(RelationEntityType::CHAT, $chat->getId(), $users);
	}

	protected function filterManualAddedUsers(\Bitrix\Im\V2\Chat $chat, array $users): array
	{
		$filteredUsers = [];
		$relations = $chat->getRelations();
		foreach ($users as $user)
		{
			$relation = $relations->getByUserId($user, $chat->getId());
			if ($relation !== null && $relation->getReason() !== Reason::DEFAULT)
			{
				$filteredUsers[] = $user;
			}
		}

		return $filteredUsers;
	}

	protected function getUsers(QueueItem $item): array
	{
		$members = $this->memberService->getPagedEmployees(
			$item->syncInfo->nodeId,
			$item->syncInfo->withChildNodes,
			$item->pointer,
			static::getLimit()
		);
		$userIds = [];

		foreach ($members as $member)
		{
			$userIds[] = $member->entityId;
		}

		return $userIds;
	}

	protected function getEntityType(): EntityType
	{
		return EntityType::CHAT;
	}

	public function finalizeSync(QueueItem $item): Result
	{
		$result = parent::finalizeSync($item);
		$this->sendFinishMessage($item);

		return $result;
	}

	protected function sendFinishMessage(QueueItem $item): void
	{
		$chat = \Bitrix\Im\V2\Chat::getInstance($item->syncInfo->entityId);
		if ($chat instanceof OpenChannelChat)
		{
			return;
		}

		$node = $this->nodeService->getNodeInformation($item->syncInfo->nodeId);
		$nodeName = $node->name;
		$postfix = $item->syncInfo->direction === SyncDirection::ADD ? 'ADD' : 'DELETE';
		$postfix .= ($postfix === 'ADD') ? '_MSGVER_1' : '';

		\CIMMessenger::Add([
			'MESSAGE' => Loc::getMessage("IM_HR_INTEGRATION_CHAT_FINISH_{$postfix}", ['#DEPARTMENT_NAME#' => $nodeName]),
			'FROM_USER_ID' => 0,
			'TO_CHAT_ID' => $chat->getId(),
			'MESSAGE_TYPE' => $chat->getType(),
			'SYSTEM' => 'Y',
		]);
	}
}
