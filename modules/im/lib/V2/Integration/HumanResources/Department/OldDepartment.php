<?php

namespace Bitrix\Im\V2\Integration\HumanResources\Department;

use Bitrix\Main\Loader;
use CIntranetUtils;

class OldDepartment extends BaseDepartment
{
	public function getTopId(): ?int
	{
		if (!Loader::includeModule("iblock"))
		{
			return null;
		}

		if (self::$wasSearchedTopId)
		{
			return self::$topId;
		}

		self::$wasSearchedTopId = true;

		$departmentId = null;
		$res = \CIBlock::GetList([], ["CODE" => "departments"]);
		if ($iblock = $res->Fetch())
		{
			$res = \CIBlockSection::GetList(
				[],
				[
					"SECTION_ID" => 0,
					"IBLOCK_ID" => $iblock["ID"]
				]
			);
			if ($department = $res->Fetch())
			{
				$departmentId = (int)$department['ID'];
			}
		}
		self::$topId = $departmentId;

		return self::$topId;
	}

	public function getList(): array
	{
		if (!empty($this->structureDepartments))
		{
			return $this->structureDepartments;
		}

		if (
			Loader::includeModule('iblock')
			&& Loader::includeModule('intranet')
		)
		{
			$departments = CIntranetUtils::GetStructureWithoutEmployees(false)['DATA'] ?? [];

			foreach ($departments as $department)
			{
				$this->structureDepartments[$department['ID']] = $this->formatDepartment($department);
			}
		}

		return $this->structureDepartments;
	}

	public function getListByIds(array $ids): array
	{
		$departments = $this->getList();

		$departmentsByIds = [];
		foreach ($departments as $department)
		{
			if (in_array($department->id, $ids, true))
			{
				$departmentsByIds[$department->id] = $department;
			}
		}

		return $departmentsByIds;
	}

	public function getListByXml(string $xmlId): array
	{
		if (!Loader::includeModule('iblock'))
		{
			return [];
		}

		$departmentRootId = \Bitrix\Main\Config\Option::get('intranet', 'iblock_structure', 0);
		if($departmentRootId <= 0)
		{
			return [];
		}

		$departments = \CIBlockSection::GetList(
			[],
			[
				'=ACTIVE' => 'Y',
				'=IBLOCK_ID' => $departmentRootId,
				'=XML_ID' => $xmlId
			]
		);

		$result = [];
		while ($row = $departments->fetch())
		{
			$result[] = $this->formatDepartment($row);
		}

	return $result;
	}

	public function getEmployeeIdsWithLimit(array $ids, int $limit = 50): array
	{
		if (!Loader::includeModule('intranet'))
		{
			return [];
		}

		$structure = \CIntranetUtils::GetStructure();

		if (!$structure || !isset($structure['DATA']))
		{
			return [];
		}

		$employees = $managers = [];
		foreach ($structure['DATA'] as $department)
		{
			if (!in_array((int)$department['ID'], $ids, true))
			{
				continue;
			}

			if (!is_array($department['EMPLOYEES']))
			{
				$employees[$department['ID']] = [];
				continue;
			}

			foreach ($department['EMPLOYEES'] as $value)
			{
				$value = (int)$value;
				if ($department['UF_HEAD'] === $value)
				{
					$managers[$value] = $value;
				}
				else
				{
					$employees[$value] = $value;
				}
			}
		}

		$allUsers = $managers + $employees;

		return array_splice($allUsers, 0, $limit);
	}

	protected function formatDepartment(array $department): Entity
	{
		return new Entity(
			name: (string)$department['NAME'],
			headUserID: isset($department['UF_HEAD']) ? (int)$department['UF_HEAD'] : 0,
			id: isset($department['ID']) ? (int)$department['ID'] : null,
			depthLevel: isset($department['DEPTH_LEVEL']) ? ((int)$department['DEPTH_LEVEL'] - 1) : null,
			parent: isset($department['IBLOCK_SECTION_ID']) ? (int)$department['IBLOCK_SECTION_ID']: null
		);
	}
}
