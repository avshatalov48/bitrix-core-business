<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Handler\Trait;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Socialnetwork\Collab\Log\CollabLogEntryCollection;
use Bitrix\Socialnetwork\Collab\Log\Entry\AddUserToCollabLogEntry;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;

trait AddMemberLogTrait
{
	/** @param array<int>$addedMembers */
	private function writeAddMemberLog(array $addedMembers, int $collabId, int $initiatorId, string $role): HandlerResult
	{
		$handlerResult = new HandlerResult();

		if (empty($addedMembers))
		{
			return $handlerResult;
		}

		$collection = new CollabLogEntryCollection();

		foreach ($addedMembers as $addedMember)
		{
			$logEntry = new AddUserToCollabLogEntry(userId: $addedMember, collabId: $collabId);
			$logEntry->setRole($role);
			$logEntry->setInitiator($initiatorId);
			$collection->add($logEntry);
		}

		$service = ServiceLocator::getInstance()->get('socialnetwork.collab.log.service');
		$service->saveCollection($collection);

		return $handlerResult;
	}
}
