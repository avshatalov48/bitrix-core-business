<?php

namespace Bitrix\Calendar\Integration\HumanResources;

use Bitrix\HumanResources\Item\Structure;
use Bitrix\HumanResources\Service\Container;

class EmployeeService
{
	public function getUserIds(int $limit, int $offset): array
	{
		if (!$this->isModuleInstalled())
		{
			return [];
		}

		$structure = Container::getStructureRepository()->getByXmlId(Structure::DEFAULT_STRUCTURE_XML_ID);
		$rootNode = Container::getNodeRepository()->getRootNodeByStructureId($structure->id);

		$employees = Container::getNodeMemberService()->getPagedEmployees(
			$rootNode->id,
			true,
			$offset,
			$limit
		);

		$userIds = [];

		foreach ($employees as $employee)
		{
			$userIds[] = $employee->entityId;
		}

		return $userIds;
	}

	private function isModuleInstalled(): bool
	{
		return \Bitrix\Main\Loader::includeModule('humanresources');
	}
}