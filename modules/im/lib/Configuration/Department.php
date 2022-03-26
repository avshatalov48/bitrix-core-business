<?php

namespace Bitrix\Im\Configuration;

use Bitrix\Main\Loader;

class Department
{
	/** @var int */
	private $id;

	public function __construct(int $id = 0)
	{
		if ($id > 0)
		{
			$this->id = $id;
		}
	}

	/**
	 * @param int $id
	 */
	public function setId(int $id): void
	{
		$this->id = $id;
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function getPathFromHeadToDepartment(): array
	{
		if (!Loader::includeModule('iblock'))
		{
			return [];
		}

		$departmentTree = \CIntranetUtils::GetDeparmentsTree(0);
		$topDepartmentId = self::getTopDepartmentId();

		if (!$topDepartmentId || empty($departmentTree) || !$this->id)
		{
			return [];
		}

		$path[] = $this->id;
		$departmentId = $this->id;

		while ($departmentId && $departmentId != $topDepartmentId)
		{
			$departmentId = $this->getHeadDepartmentId($departmentId, $departmentTree) ?? $topDepartmentId;
			$path[] = $departmentId;
		}

		return array_reverse($path);
	}

	/**
	 * @param int $curId
	 * @param array $departmentTree
	 *
	 * @return int|null
	 */
	protected function getHeadDepartmentId(int $curId, array $departmentTree): ?int
	{
		foreach ($departmentTree as $headDepartmentId => $subDepartments)
		{
			foreach ($subDepartments as $subDepartmentId)
			{
				if ((int)$subDepartmentId == $curId)
				{
					return (int)$headDepartmentId;
				}
			}
		}

		return null;
	}

	/**
	 * @param array $departmentIds
	 *
	 * @return array
	 */
	public function getAccessCodes(array $departmentIds): array
	{
		$accessCodes = [];
		foreach ($departmentIds as $departmentId)
		{
			if ((int)$departmentId === $this->id)
			{
				$accessCodes[] = 'D' . $this->id;
				$accessCodes[] = 'DR' . $this->id;
			}
			else
			{
				$accessCodes[] = 'DR' . $departmentId;
			}
		}
		return $accessCodes;
	}

	public static function getTopDepartmentId()
	{
		if (!Loader::includeModule("iblock"))
		{
			return false;
		}

		$departmentId = false;
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

		return $departmentId;
	}
}
