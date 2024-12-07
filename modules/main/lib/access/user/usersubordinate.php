<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Access\User;


class UserSubordinate
{
	public const
		RELATION_OTHER 			= 0,
		RELATION_HIMSELF		= 1,
		RELATION_DEPARTMENT 	= 2,
		RELATION_SUBORDINATE 	= 3,
		RELATION_DIRECTOR 		= 4,
		RELATION_OTHER_DIRECTOR = 5;

	private $userId;

	private static $cache = [];

	private static $structure;

	public static function getDepartmentsByUserId(int $userId): array
	{
		$key = 'DEP_'.$userId;
		if (!array_key_exists($key, static::$cache))
		{
			$res = \Bitrix\Main\UserTable::getList(
				[
					'filter' => [
						'=ID' => $userId,
					],
					'select' => ['UF_DEPARTMENT']
				]
			);

			$departments = [];
			while ($row = $res->fetch())
			{
				if (is_array($row['UF_DEPARTMENT']))
				{
					$departments = array_merge($departments, $row['UF_DEPARTMENT']);
				}
			}

			static::$cache[$key] = $departments;
		}

		return static::$cache[$key];
	}

	public function __construct(int $userId)
	{
		$this->userId = $userId;
	}

	public function getSubordinate(int $userId): int
	{
		if ($this->userId === $userId)
		{
			return self::RELATION_HIMSELF;
		}

		if (!\CModule::IncludeModule('intranet'))
		{
			return self::RELATION_OTHER;
		}

		$key = 'SUB_' . $this->userId .'_'. $userId;
		if (
			array_key_exists($key, static::$cache)
		)
		{
			return static::$cache[$key];
		}

		$managers = self::getAllManagers();

		$selfDepartments = self::getUserDepartments($this->userId);
		$foreignDepartments = self::getUserDepartments($userId);

		$inDepartment = !empty(array_intersect($selfDepartments, $foreignDepartments));

		$selfManagers = $this->getDepartmentsManagers($selfDepartments);
		$foreignManagers = $this->getDepartmentsManagers($foreignDepartments, true);

		if (in_array($this->userId, $foreignManagers))
		{
			static::$cache[$key] = self::RELATION_SUBORDINATE;
		}
		elseif (in_array($userId, $selfManagers))
		{
			static::$cache[$key] = self::RELATION_DIRECTOR;
		}
		elseif ($inDepartment)
		{
			static::$cache[$key] = self::RELATION_DEPARTMENT;
		}
		elseif (in_array($userId, $managers))
		{
			static::$cache[$key] = self::RELATION_OTHER_DIRECTOR;
		}
		else
		{
			static::$cache[$key] = self::RELATION_OTHER;
		}

		return static::$cache[$key];
	}

	public static function getParentDepartments(int $id): array
	{
		$structure = self::getStructure();
		$res = [];
		foreach ($structure['TREE'] as $parent => $childs)
		{
			if (!is_array($childs))
			{
				continue;
			}
			if ($parent > 0 && in_array($id, $childs))
			{
				$res[] = $parent;
				$res = array_merge($res, self::getParentDepartments($parent));
			}
		}

		return $res;
	}

	private function getDepartmentsManagers($departments, bool $recursive = false): array
	{
		$managersIds = [];

		$deps = [];
		foreach ($departments as $depId)
		{
			$deps[] = $depId;
			if ($recursive)
			{
				$deps = array_merge($deps, self::getParentDepartments($depId));
			}
		}
		$deps = array_unique($deps);

		$structure = self::getStructure();
		foreach ($structure['DATA'] as $row)
		{
			if (in_array($row['ID'], $deps))
			{
				$id = (int) $row['UF_HEAD'];
				$managersIds[$id] = $id;
			}
		}

		return $managersIds;
	}

	private static function getStructure()
	{
		if (!static::$structure)
		{
			static::$structure = \CIntranetUtils::GetStructure();
		}
		return static::$structure;
	}

	private static function getAllManagers(): array
	{
		$managers = [];

		$structure = self::getStructure();
		foreach ($structure['DATA'] as $row)
		{
			$managers[$row['ID']] = $row['UF_HEAD'];
		}

		return $managers;
	}

	private static function getUserDepartments(int $userId)
	{
		$structure = self::getStructure();
		$departments = [];
		foreach ($structure['DATA'] as $row)
		{
			if (in_array($userId, $row['EMPLOYEES']))
			{
				$departments[$row['ID']] = $row['ID'];
			}
		}
		return $departments;
	}
}