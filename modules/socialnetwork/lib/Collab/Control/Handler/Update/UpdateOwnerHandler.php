<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Handler\Update;

use Bitrix\Main\Error;
use Bitrix\SocialNetwork\Collab\Analytics\CollabAnalytics;
use Bitrix\Socialnetwork\Collab\Control\Command\CollabUpdateCommand;
use Bitrix\Socialnetwork\Collab\Control\Handler\Trait\UpdateMemberRoleLogTrait;
use Bitrix\Socialnetwork\Control\Command\UpdateCommand;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;
use Bitrix\Socialnetwork\Control\Handler\Update\UpdateHandlerInterface;
use Bitrix\Socialnetwork\Control\Member\Trait\UpdateMemberRoleTrait;
use Bitrix\Socialnetwork\Item\Workgroup;
use Bitrix\Socialnetwork\UserToGroupTable;
use CSocNetUserToGroup;

class UpdateOwnerHandler implements UpdateHandlerInterface
{
	use UpdateMemberRoleLogTrait;
	use UpdateMemberRoleTrait;

	public function update(UpdateCommand $command, Workgroup $entityBefore, Workgroup $entityAfter): HandlerResult
	{
		$handlerResult = new HandlerResult();

		if (!$command instanceof CollabUpdateCommand)
		{
			$handlerResult->addError(new Error('Unexpected command type'));

			return $handlerResult;
		}

		$ownerId = $command->getOwnerId();
		if ($ownerId <= 0)
		{
			return $handlerResult;
		}

		$beforeOwnerId = $entityBefore->getOwnerId();
		if ($beforeOwnerId === $ownerId)
		{
			return $handlerResult;
		}

		$result = CSocNetUserToGroup::SetOwner($ownerId, $command->getId(), [], true);

		if ($result === false)
		{
			$handlerResult->addApplicationError();
		}

		$result = $this->updateMembersRole($command->getId(), $command->getInitiatorId(), UserToGroupTable::ROLE_MODERATOR, $beforeOwnerId);

		$handlerResult->merge($result);

		$oldOwnerChangeRoleLogResult =
			$this->writeMemberRoleUpdateLog(
				[$entityBefore->getOwnerId()],
				$command->getId(),
				$command->getInitiatorId(),
				UserToGroupTable::ROLE_MODERATOR,
			)
		;

		$newOwnerChangeRoleLogResult =
			$this->writeMemberRoleUpdateLog(
				[$command->getOwnerId()],
				$command->getId(),
				$command->getInitiatorId(),
				UserToGroupTable::ROLE_OWNER,
			)
		;

		$handlerResult
			->merge($oldOwnerChangeRoleLogResult)
			->merge($newOwnerChangeRoleLogResult)
		;

		CollabAnalytics::getInstance()->onOwnerChanged($command->getInitiatorId(), $command->getId());

		return $handlerResult;
	}
}
