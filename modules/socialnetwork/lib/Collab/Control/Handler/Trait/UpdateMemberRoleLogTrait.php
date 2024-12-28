<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Handler\Trait;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Socialnetwork\Collab\Log\CollabLogEntryCollection;
use Bitrix\Socialnetwork\Collab\Log\Entry\ChangeCollabMemberRoleLogEntry;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;

trait UpdateMemberRoleLogTrait
{
	private function writeMemberRoleUpdateLog(array $userIds, int $collabId, int $initiatorId ,string $newRole): HandlerResult
	{
		$handlerResult = new HandlerResult();

		if (empty($userIds))
		{
			return $handlerResult;
		}

		$logEntryCollection = new CollabLogEntryCollection();

		foreach ($userIds as $userId)
		{
			$logEntry = new ChangeCollabMemberRoleLogEntry(userId: $userId, collabId: $collabId);
			$logEntry->setNewRole($newRole);
			$logEntry->setInitiator($initiatorId);

			$logEntryCollection->add($logEntry);
		}

		$service = ServiceLocator::getInstance()->get('socialnetwork.collab.log.service');
		$service->saveCollection($logEntryCollection);

		return $handlerResult;
	}
}
