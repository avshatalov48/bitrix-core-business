<?php

namespace Bitrix\Bizproc\Workflow\Type;

class GlobalVar
{
	private static $allCache;

	/**
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getAll(): array
	{
		if (self::$allCache === null)
		{
			$all = [];
			$listResult = Entity\GlobalVarTable::getList();

			foreach ($listResult as $row)
			{
				$all[$row['ID']] = Entity\GlobalVarTable::convertToProperty($row);
			}

			self::$allCache = $all;
		}

		return self::$allCache;
	}

	/**
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getById($id)
	{
		$all = static::getAll();

		return $all[$id] ?? null;
	}

	/**
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getValue($id)
	{
		$property = is_array($id) ? $id : static::getById($id);

		return $property ? $property['Default'] : null;
	}

	/**
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function upsert($id, $property): bool
	{
		Entity\GlobalVarTable::upsertByProperty($id, $property);
		self::$allCache = null;

		return true;
	}

	/**
	 * @throws \Exception
	 */
	public static function delete($id): bool
	{
		Entity\GlobalVarTable::delete($id);
		self::$allCache = null;

		return true;
	}

	/**
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Exception
	 */
	public static function saveAll(array $all): bool
	{
		$diff = array_diff(array_keys(static::getAll()), array_keys($all));

		foreach ($all as $id => $property)
		{
			if (!isset($property['Changed']) || \CBPHelper::getBool($property['Changed']) === true)
			{
				static::upsert($id, $property);
			}
		}

		if ($diff)
		{
			foreach ($diff as $toDelete)
			{
				static::delete($toDelete);
			}
		}

		self::$allCache = null;

		return true;
	}
}
