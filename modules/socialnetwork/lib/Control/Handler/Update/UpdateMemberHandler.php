<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Handler\Update;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Socialnetwork\Control\Command\UpdateCommand;
use Bitrix\Socialnetwork\Control\GroupResult;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;
use Bitrix\Socialnetwork\Integration\HumanResources\AccessCodeConverter;
use Bitrix\Socialnetwork\Item\Workgroup;

class UpdateMemberHandler implements UpdateHandlerInterface
{
	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 * @throws LoaderException

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

		$membersByCommand = (new AccessCodeConverter(...$command->getAddMembers()))->getUserIds();

		//When remove a department from group members, only synchronization with it is deleted - members remain
		$membersAfterUpdate = array_merge($membersByCommand, $currentMembersByDepartments);

		$membersToExclude = array_diff($currentMembers, $membersAfterUpdate);
		foreach ($membersToExclude as $memberId)
		{
			\Bitrix\Socialnetwork\Helper\Workgroup::exclude([
				'groupId' => $entityAfter->getId(),
				'userId' => $memberId,
			]);
		}

		return $handlerResult;
	}

	/**
	 * @throws LoaderException
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @return int[]
	 */
	private function getDepartmentsMemberList(Workgroup $workgroup): array
	{
		$departmentIds = $workgroup->getSynchronizedDepartmentIds();
		$departmentAccessCodes = array_map(
			static fn(int $departmentId): string => "DR${departmentId}",
			$departmentIds
		);

		return (new AccessCodeConverter(...$departmentAccessCodes))->getUserIds();
	}
}
