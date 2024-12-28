<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Handler\Update;

use Bitrix\SocialNetwork\Collab\Analytics\CollabAnalytics;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Control\Handler\Trait\AddMemberLogTrait;
use Bitrix\Socialnetwork\Collab\Control\Handler\Trait\UpdateMemberRoleLogTrait;
use Bitrix\Socialnetwork\Collab\Integration\IM\ActionMessageBuffer;
use Bitrix\Socialnetwork\Collab\Integration\IM\Messenger;
use Bitrix\Socialnetwork\Control\Member\Trait\AddMemberTrait;
use Bitrix\Socialnetwork\Control\Member\Trait\GetMembersTrait;
use Bitrix\Socialnetwork\Collab\Integration\IM\ActionType;
use Bitrix\Socialnetwork\Control\Command\UpdateCommand;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;
use Bitrix\Socialnetwork\Control\Handler\Update\UpdateHandlerInterface;
use Bitrix\Socialnetwork\Control\Member\Trait\UpdateMemberRoleTrait;
use Bitrix\Socialnetwork\Integration\HumanResources\AccessCodeConverter;
use Bitrix\Socialnetwork\Item\Workgroup;
use Bitrix\Socialnetwork\UserToGroupTable;

class UpdateModeratorsHandler implements UpdateHandlerInterface
{
	use GetMembersTrait;
	use AddMemberTrait;
	use UpdateMemberRoleTrait;
	use AddMemberLogTrait;
	use UpdateMemberRoleLogTrait;

	public function update(UpdateCommand $command, Workgroup $entityBefore, Workgroup $entityAfter): HandlerResult
	{
		$handlerResult = new HandlerResult();

		$commandAddModeratorsAccessCodes = $command->getAddModeratorMembers();
		if (!empty($commandAddModeratorsAccessCodes))
		{
			$commandAddModerators = (new AccessCodeConverter(...$commandAddModeratorsAccessCodes))
				->getUsers()
				->getUserIds()
			;

			$add = array_diff($commandAddModerators, $this->getMemberIds($command->getId()));
			$handlerResult = $this->addModeratorMembers($command, $add, $entityAfter);

			$update = [];
			$currentMembers = $this->getMemberIds($command->getId());
			foreach ($commandAddModerators as $moderatorId)
			{
				$isCurrentMember = in_array($moderatorId, $currentMembers, true);
				$isAlreadyAdd = in_array($moderatorId, $add, true);
				$isOwner = $moderatorId === $entityAfter->getOwnerId();

				if ($isCurrentMember && !$isAlreadyAdd && !$isOwner)
				{
					$update[] = $moderatorId;
				}
			}

			if (!empty($update))
			{
				$handlerResult->merge($this->increaseMembersRole($command, $update));
			}
		}

		$commandDeleteModeratorsAccessCodes = $command->getDeleteModeratorMembers();
		if (!empty($commandDeleteModeratorsAccessCodes))
		{
			$currentModerators = $this->getMemberIds($command->getId(), UserToGroupTable::ROLE_MODERATOR);
			$commandDeleteModerators = (new AccessCodeConverter(...$commandDeleteModeratorsAccessCodes))
				->getUsers()
				->getUserIds()
			;

			$delete = array_intersect($currentModerators, $commandDeleteModerators);
			if (!empty($delete))
			{
				$handlerResult->merge($this->decreaseMembersRole($command, $delete));
			}
		}

		return $handlerResult;
	}

	/**
	 * @param UpdateCommand $command
	 * @param int[] $memberIds
	 * @return HandlerResult
	 */
	private function addModeratorMembers(UpdateCommand $command, array $memberIds, Collab $entityAfter): HandlerResult
	{
		$handlerResult = $this->addMembers(
			$command->getId(),
			$command->getInitiatorId(),
			UserToGroupTable::ROLE_MODERATOR,
			...$memberIds
		);

		if (!$handlerResult->isSuccess())
		{
			return $handlerResult;
		}

		ActionMessageBuffer::getInstance()
			->put(ActionType::AddUser, $command->getId(), $command->getInitiatorId(), $memberIds)
			->flush();

		Messenger::setManagers($command->getId(), $memberIds);

		CollabAnalytics::getInstance()->onModeratorChanged($command->getInitiatorId(), $command->getId());

		$writeToLogResult = $this->writeAddMemberLog(
			$memberIds,
			$command->getId(),
			$command->getInitiatorId(),
			UserToGroupTable::ROLE_MODERATOR
		);

		return $handlerResult->merge($writeToLogResult);
	}

	/**
	 * @param UpdateCommand $command
	 * @param int[] $memberIds
	 * @return HandlerResult
	 */
	private function increaseMembersRole(UpdateCommand $command, array $memberIds): HandlerResult
	{
		$handlerResult = $this->updateMembersRole(
			$command->getId(),
			$command->getInitiatorId(),
			UserToGroupTable::ROLE_MODERATOR,
			...$memberIds
		);

		if (!$handlerResult->isSuccess())
		{
			return $handlerResult;
		}

		Messenger::setManagers($command->getId(), $memberIds);

		$writeLogResult = $this->writeMemberRoleUpdateLog(
			$memberIds,
			$command->getId(),
			$command->getInitiatorId(),
			UserToGroupTable::ROLE_MODERATOR
		);

		return $handlerResult->merge($writeLogResult);
	}

	private function decreaseMembersRole(UpdateCommand $command, array $memberIds): HandlerResult
	{
		$handlerResult = $this->updateMembersRole(
			$command->getId(),
			$command->getInitiatorId(),
			UserToGroupTable::ROLE_USER,
			...$memberIds
		);

		if (!$handlerResult->isSuccess())
		{
			return $handlerResult;
		}

		Messenger::unsetManagers($command->getId(), $memberIds);

		$writeLogResult = $this->writeMemberRoleUpdateLog(
			$memberIds,
			$command->getId(),
			$command->getInitiatorId(),
			UserToGroupTable::ROLE_USER
		);

		return $handlerResult->merge($writeLogResult);
	}
}