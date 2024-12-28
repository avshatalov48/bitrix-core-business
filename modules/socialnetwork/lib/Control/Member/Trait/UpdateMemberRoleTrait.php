<?php

namespace Bitrix\Socialnetwork\Control\Member\Trait;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;
use Bitrix\Socialnetwork\UserToGroupTable;
use CSocNetUserToGroup;

trait UpdateMemberRoleTrait
{
	private function updateMembersRole(int $groupId, int $initiatorId, string $role, int ...$members): HandlerResult
	{
		$handlerResult = new HandlerResult();

		if (empty($members))
		{
			return $handlerResult;
		}

		$handlerResult->setGroupChanged();

		$relationList = $this->getRelationList($groupId, ...$members);
		foreach ($members as $userId)
		{
			$user = [
				'ROLE' => $role,
				'DATE_CREATE' => new DateTime(),
				'DATE_UPDATE' => new DateTime(),
				'INITIATED_BY_TYPE' => UserToGroupTable::INITIATED_BY_GROUP,
				'INITIATED_BY_USER_ID' => $initiatorId,
			];

			if (!isset($relationList[$userId]))
			{
				$handlerResult->addError(new Error("User {$userId} not found in group {$groupId}"));
			}

			$result = CSocNetUserToGroup::Update($relationList[$userId], $user);
			if ($result === false)
			{
				$handlerResult->addApplicationError();
			}
		}

		return $handlerResult;
	}

	/**
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function getRelationList(int $groupId, int ...$memberIds): array
	{
		$relationCollection = UserToGroupTable::query()
			->setSelect(['ID', 'USER_ID', 'GROUP_ID'])
			->setFilter([
				'@USER_ID' => $memberIds,
				'=GROUP_ID' => $groupId,
			])
			->fetchCollection()
		;

		$result = [];
		foreach ($relationCollection as $relation)
		{
			$result[$relation['USER_ID']] = $relation['ID'];
		}

		return $result;
	}
}