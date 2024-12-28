<?php

namespace Bitrix\Socialnetwork\Integration\HumanResources;

use Bitrix\HumanResources\Exception\WrongStructureItemException;
use Bitrix\HumanResources\Service\Container;
use Bitrix\HumanResources\Type\MemberEntityType;
use Bitrix\HumanResources\Type\NodeEntityType;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\EO_User_Collection;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

class EmployeeHelper
{
	/**
	 * Retrieves a mapping of employees to their respective department nodes.
	 * @return array
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function employeesToDepartment(EO_User_Collection $usersCollection): array
	{
		if (!Loader::includeModule('humanresources'))
		{
			return [];
		}
		$employeesNodeMap = [];

		foreach ($usersCollection as $user)
		{
			$nodeMember = Container::getNodeMemberRepository()
				->findFirstByEntityIdAndEntityTypeAndNodeTypeAndActive(
					$user->getId(),
					MemberEntityType::USER,
					NodeEntityType::DEPARTMENT,
				);

			if ($nodeMember)
			{
				$employeesNodeMap[$nodeMember->entityId] = $nodeMember->nodeId;
			}
		}

		return $employeesNodeMap;
	}
}