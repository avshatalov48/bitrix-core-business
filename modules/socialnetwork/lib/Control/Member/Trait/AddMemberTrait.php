<?php

namespace Bitrix\Socialnetwork\Control\Member\Trait;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Type\DateTime;
use Bitrix\Socialnetwork\Collab\Control\Invite\Command\InvitationCommand;
use Bitrix\Socialnetwork\Collab\Permission\UserRole;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;
use Bitrix\Socialnetwork\UserToGroupTable;
use CSocNetUserToGroup;

trait AddMemberTrait
{
	private function addMembers(int $groupId, int $initiatorId, string $role, int ...$members): HandlerResult
	{
		$handlerResult = new HandlerResult();

		if (empty($members))
		{
			return $handlerResult;
		}

		$handlerResult->setGroupChanged();

		foreach ($members as $userId)
		{
			$user = [
				'USER_ID' => $userId,
				'GROUP_ID' => $groupId,
				'ROLE' => $role,
				'DATE_CREATE' => new DateTime(),
				'DATE_UPDATE' => new DateTime(),
				'INITIATED_BY_TYPE' => UserToGroupTable::INITIATED_BY_GROUP,
				'INITIATED_BY_USER_ID' => $initiatorId,
			];

			$result = CSocNetUserToGroup::Add($user, true, true, true);
			if ($result === false)
			{
				$handlerResult->addApplicationError();
			}
		}

		return $handlerResult;
	}

	private function deleteMembers(int $groupId, int...$members): HandlerResult
	{
		$handlerResult = new HandlerResult();

		if (empty($members))
		{
			return $handlerResult;
		}

		$relations = UserToGroupTable::query()
			->setSelect(['ID'])
			->where('GROUP_ID', $groupId)
			->whereIn('USER_ID', $members)
			->exec()
			->fetchAll();

		$relations = array_column($relations, 'ID');

		foreach ($relations as $relationId)
		{
			$result = CSocNetUserToGroup::Delete($relationId, false, true, true, true);
			if ($result === false)
			{
				$handlerResult->addApplicationError();
			}
		}

		return $handlerResult;
	}

	private function inviteMembers(int $groupId, int $initiatorId, int ...$members): HandlerResult
	{
		$handlerResult = new HandlerResult();

		if (empty($members))
		{
			return $handlerResult;
		}

		$invitationService = ServiceLocator::getInstance()->get('socialnetwork.collab.invitation.service');

		$handlerResult->setGroupChanged();

		foreach ($members as $userId)
		{
			$user = [
				'USER_ID' => $userId,
				'GROUP_ID' => $groupId,
				'ROLE' => UserRole::REQUEST,
				'DATE_CREATE' => new DateTime(),
				'DATE_UPDATE' => new DateTime(),
				'INITIATED_BY_TYPE' => UserToGroupTable::INITIATED_BY_GROUP,
				'INITIATED_BY_USER_ID' => $initiatorId,
			];

			$result = CSocNetUserToGroup::Add($user, true, true, true);
			if ($result === false)
			{
				$handlerResult->addApplicationError();
			}

			$command = (new InvitationCommand())
				->setInitiatorId($initiatorId)
				->setRecipientId($userId)
				->setCollabId($groupId)
				->setRelationId($result)
			;

			$invitationResult = $invitationService->send($command);

			$handlerResult->merge($invitationResult);
		}

		return $handlerResult;
	}
}