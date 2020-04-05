<?php
namespace Bitrix\Bizproc\Workflow\Type;

use Bitrix\Bizproc\FieldType;
use Bitrix\Main;

class GlobalConst
{
	const CONF_NAME = 'global_const';
	private static $allCache;

	public static function getAll()
	{
		if (self::$allCache !== null)
		{
			return self::$allCache;
		}

		$all = [];
		$res = Entity\GlobalConstTable::getList(['order' => ['ID' => 'ASC']]);
		while ($row = $res->fetch())
		{
			$all[$row['ID']] = Entity\GlobalConstTable::convertToProperty($row);
		}

		return (self::$allCache = $all);
	}

	public static function getById($id)
	{
		$all = static::getAll();
		return isset($all[$id]) ? $all[$id] : null;
	}

	/**
	 * Gets value of constant.
	 * @param array|string $constId Constant Id or constant property.
	 * @return mixed|null Constant value.
	 */
	public static function getValue($constId)
	{
		$property = is_array($constId) ? $constId : static::getById($constId);
		return $property ? $property['Default'] : null;
	}

	public static function upsert($constId, $property)
	{
		$all = static::getAll();
		$prevProperty = static::getById($constId);

		if ($prevProperty)
		{
			$property += $prevProperty;
		}

		$all[$constId] = $property + $prevProperty;
		return static::saveAll($all);
	}

	public static function delete($constId)
	{
		$all = static::getAll();
		unset($all[$constId]);
		return static::saveAll($all);
	}

	public static function saveAll(array $all)
	{
		$diff = array_diff(array_keys(static::getAll()), array_keys($all));

		foreach ($all as $id => $property)
		{
			$all[$id] = FieldType::normalizeProperty($property);
			Entity\GlobalConstTable::upsertByProperty($id, $property);
		}

		if ($diff)
		{
			foreach ($diff as $toDelete)
			{
				Entity\GlobalConstTable::delete($toDelete);
			}
		}

		//clear cache
		self::$allCache = null;

		return true;
	}
}