<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Handler\Update;

use Bitrix\Main\AccessDeniedException;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Bitrix\Socialnetwork\Control\Command\UpdateCommand;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;
use Bitrix\Socialnetwork\Integration\HumanResources\AccessCodeConverter;
use Bitrix\Socialnetwork\Item\Workgroup;

class ExcludeMemberHandler implements UpdateHandlerInterface
{
	/**
	 * @throws ObjectPropertyException
	 * @throws LoaderException
	 * @throws ArgumentException
	 * @throws AccessDeniedException
	 * @throws SystemException
	 */
	public function update(UpdateCommand $command, Workgroup $entityBefore, Workgroup $entityAfter): HandlerResult
	{
		$handlerResult = new HandlerResult();
		if ($command->getAddMembers() === null)
		{
			return $handlerResult;
		}

		$currentMembers = $entityBefore->getUserMemberIds();
		$currentMembersByDepartments = $this->getDepartmentsMemberList($entityBefore);
		$commandUserList = (new AccessCodeConverter(...$command->getAddMembers()))->getUserIds();

		$membersToExclude = $this->getMembersToExclude($currentMembers, $currentMembersByDepartments, $commandUserList);
		if (empty($membersToExclude))
		{
			return $handlerResult;
		}

		foreach ($membersToExclude as $memberId)
		{
			$excludeResult = $this->excludeMember($entityBefore->getId(), $memberId);
			$handlerResult->merge($excludeResult);
		}

		$handlerResult->setGroupChanged();

		return $handlerResult;
	}

	private function getMembersToExclude(
		array $currentMembers,
		array $currentMembersByDepartment,
		array $commandUsers,
	): array
	{
		$membersToExclude = [];
		foreach ($currentMembers as $member)
		{
			$isCommandUser = in_array($member, $commandUsers, true);
			$isGroupMemberByDepartment = in_array($member, $currentMembersByDepartment, true);

			//When remove a department from group members, only synchronization with it is deleted - members remain
			if ($isCommandUser || $isGroupMemberByDepartment)
			{
				continue;
			}

			$membersToExclude[] = $member;
		}

		return $membersToExclude;
	}

	/**
	 * @throws LoaderException
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @return int[]
	 */
	private function getDepartmentsMemberList(Workgroup $entity): array
	{
		$departmentIds = $entity->getSynchronizedDepartmentIds();
		$departmentAccessCodes = array_map(
			static fn(int $departmentId): string => "DR${departmentId}",
			$departmentIds
		);

		return (new AccessCodeConverter(...$departmentAccessCodes))->getUserIds();
	}

	private function excludeMember(int $entityId, int $memberId): Result
	{
		$result = new Result();

		try
		{
			\Bitrix\Socialnetwork\Helper\Workgroup::exclude([
				'groupId' => $entityId,
				'userId' => $memberId,
			]);
		}
		catch (AccessDeniedException $e)
		{
			$result->addError(new Error("Deletion member with id $memberId was failed"));
		}

		return $result;
	}
}
