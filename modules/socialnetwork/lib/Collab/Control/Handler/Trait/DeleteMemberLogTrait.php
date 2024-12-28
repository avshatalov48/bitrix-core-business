<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Handler\Trait;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Socialnetwork\Collab\Log\CollabLogEntryCollection;
use Bitrix\Socialnetwork\Collab\Log\Entry\RemoveUserFromCollabLogEntry;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;
use Bitrix\Socialnetwork\Item\Workgroup;
use Bitrix\Socialnetwork\UserToGroupTable;

trait DeleteMemberLogTrait
{
	private function writeDeleteMemberLog(array $deletedMembers, Workgroup $entityAfter, int $initiatorId): HandlerResult
	{
		$handlerResult = new HandlerResult();

		if (empty($deletedMembers))
		{
			return $handlerResult;
		}

		if (!$entityAfter->isCollab())
		{
			return $handlerResult;
		}

		$ownerId = $entityAfter->getOwnerId();
		$moderators = $entityAfter->getModeratorMemberIds();

		$collection = new CollabLogEntryCollection();

		foreach ($deletedMembers as $deletedMember)
		{
			$role = UserToGroupTable::ROLE_USER;

			if ($deletedMember === $ownerId)
			{
				$role = UserToGroupTable::ROLE_OWNER;
			}
			elseif (in_array($deletedMember, $moderators, true))
			{
				$role = UserToGroupTable::ROLE_MODERATOR;
			}

			$logEntry = new RemoveUserFromCollabLogEntry(userId: $deletedMember, collabId: $entityAfter->getId());
			$logEntry->setRole($role);
			$logEntry->setInitiator($initiatorId);
			$collection->add($logEntry);
		}

		$service = ServiceLocator::getInstance()->get('socialnetwork.collab.log.service');
		$service->saveCollection($collection);

		return $handlerResult;
	}
}
