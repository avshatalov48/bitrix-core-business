<?php

namespace Bitrix\Im\V2\Integration\HumanResources\Department;

abstract class BaseDepartment implements IDepartment
{
	protected static ?int $topId = null;
	protected static bool $wasSearchedTopId = false;
	protected array $structureDepartments = [];

	public function getTopCode(): ?string
	{
		$departmentId = $this->getTopId();

		if (!isset($departmentId))
		{
			return null;
		}

		return 'DR' . $departmentId;
	}

	public function getColleagues(): array
	{
		$result = \CIntranetUtils::getDepartmentColleagues(null, true, false, 'Y', ['ID']);
		$colleaguesIds = [];

		while (($row = $result->Fetch()))
		{
			$id = (int)$row['ID'];
			$colleaguesIds[$id] = $id;
		}

		return array_values($colleaguesIds);
	}
}
