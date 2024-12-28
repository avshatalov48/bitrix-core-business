<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Member;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;
use Bitrix\Socialnetwork\Control\Member\Command\MembersCommand;
use Bitrix\Socialnetwork\Control\Member\Trait\AddMemberTrait;
use Bitrix\Socialnetwork\Control\Member\Trait\UpdateMemberRoleTrait;
use Bitrix\Socialnetwork\Integration\HumanResources\AccessCodeConverter;
use Bitrix\Socialnetwork\Internals\Registry\GroupRegistry;
use Bitrix\Socialnetwork\Item\Workgroup;
use Bitrix\Socialnetwork\UserToGroupTable;
use CSocNetUserToGroup;

class GroupMemberService extends AbstractMemberService
{
	use AddMemberTrait;
	use UpdateMemberRoleTrait;

	protected function inviteImplementation(MembersCommand $command, Workgroup $group): Result
	{
		$currentMembers = $this->getMemberIds($command->getGroupId());
		$membersByCommand = (new AccessCodeConverter(...$command->getMembers()))
			->getUsers()
			->getUserIds()
		;

		$result = new Result();

		$membersToInvite = array_diff($membersByCommand, $currentMembers);
		if (empty($membersToInvite))
		{
			return $result;
		}

		foreach ($membersToInvite as $userId)
		{
			$isSuccess = CSocNetUserToGroup::SendRequestToJoinGroup(
				$command->getInitiatorId(),
				$userId,
				$command->getGroupId(),
				'',
				false,
			);

			if (!$isSuccess)
			{
				$result->addError(new Error('Cannot invite user', $userId));
			}
		}

		return $result;
	}

	protected function addImplementation(MembersCommand $command, Workgroup $group): Result
	{
		$currentMembers = $this->getMemberIds($command->getGroupId());
		$membersByCommand = (new AccessCodeConverter(...$command->getMembers()))
			->getUsers()
			->getUserIds()
		;

		$result = new Result();

		$membersToAdd = array_diff($membersByCommand, $currentMembers);
		if (empty($membersToAdd))
		{
			return $result;
		}

		foreach ($membersToAdd as $userId)
		{
			$user = [
				'USER_ID' => $userId,
				'GROUP_ID' => $command->getGroupId(),
				'ROLE' => UserToGroupTable::ROLE_USER,
				'DATE_CREATE' => new DateTime(),
				'DATE_UPDATE' => new DateTime(),
				'INITIATED_BY_TYPE' => UserToGroupTable::INITIATED_BY_GROUP,
				'INITIATED_BY_USER_ID' => $command->getInitiatorId(),
			];

			$isSuccess = CSocNetUserToGroup::Add($user);

			if (!$isSuccess)
			{
				$result->addError(new Error('Cannot add user', $userId));
			}
		}

		return $result;
	}

	protected function deleteImplementation(MembersCommand $command, Workgroup $group): Result
	{
		$result = new Result();

		$membersByCommand = (new AccessCodeConverter(...$command->getMembers()))
			->getUsers()
			->getUserIds()
		;

		if (empty($membersByCommand))
		{
			return $result;
		}

		$relations = UserToGroupTable::query()
			->setSelect(['ID'])
			->where('GROUP_ID', $command->getGroupId())
			->whereIn('USER_ID', $membersByCommand)
			->exec()
			->fetchAll()
		;

		$relations = array_column($relations, 'ID');

		foreach ($relations as $relationId)
		{
			$isSuccess = CSocNetUserToGroup::Delete($relationId);

			if (!$isSuccess)
			{
				$result->addError(new Error('Cannot add user'));
			}
		}

		return $result;
	}

	public function addModeratorsImplementation(MembersCommand $command, Workgroup $group): Result
	{
		$result = new Result();

		$commandModeratorsAccessCodes = $command->getMembers();
		if (empty($commandModeratorsAccessCodes))
		{
			return $result;
		}

		$commandModerators = (new AccessCodeConverter(...$commandModeratorsAccessCodes))
			->getUsers()
			->getUserIds()
		;

		$add = array_diff($commandModerators, $this->getMemberIds($command->getGroupId()));
		$result = $this->addModeratorMembers($command, $add);

		$update = [];
		$currentMembers = $this->getMemberIds($command->getGroupId());
		foreach ($commandModerators as $moderatorId)
		{
			$isCurrentMember = in_array($moderatorId, $currentMembers, true);
			$isAlreadyAdd =  in_array($moderatorId, $add, true);

			if ($isCurrentMember && !$isAlreadyAdd)
			{
				$update[] = $moderatorId;
			}
		}

		if (empty($update))
		{
			return $result;
		}

		$result->merge($this->increaseMembersRole($command, $update));

		return $result;
	}

	private function addModeratorMembers(MembersCommand $command, array $memberIds): HandlerResult
	{
		return $this->addMembers(
			$command->getGroupId(),
			$command->getInitiatorId(),
			UserToGroupTable::ROLE_MODERATOR,
			...$memberIds,
		);
	}

	private function increaseMembersRole(MembersCommand $command, array $memberIds): HandlerResult
	{
		return $this->updateMembersRole(
			$command->getGroupId(),
			$command->getInitiatorId(),
			UserToGroupTable::ROLE_MODERATOR,
			...$memberIds,
		);
	}

	protected function deleteModeratorsImplementation(MembersCommand $command, Workgroup $group): Result
	{
		$result = new Result();

		$commandModeratorsAccessCodes = $command->getMembers();
		if (empty($commandModeratorsAccessCodes))
		{
			return $result;
		}

		$currentModerators = $this->getMemberIds($command->getGroupId(), UserToGroupTable::ROLE_MODERATOR);
		$commandModerators = (new AccessCodeConverter(...$commandModeratorsAccessCodes))
			->getUsers()
			->getUserIds()
		;

		$delete = array_intersect($currentModerators, $commandModerators);

		if (!empty($delete))
		{
			$result = $this->decreaseMembersRole($command, $delete);
		}

		return $result;
	}

	protected function getRegistry(): GroupRegistry
	{
		return GroupRegistry::getInstance();
	}

	private function decreaseMembersRole(MembersCommand $command, array $memberIds): HandlerResult
	{
		return $this->updateMembersRole(
			$command->getGroupId(),
			$command->getInitiatorId(),
			UserToGroupTable::ROLE_USER,
			...$memberIds,
		);
	}
}
