<?php

namespace Bitrix\Catalog\Product\SystemField;

abstract class Highloadblock extends Base
{
	private static array $dictionary = [];

	private static array $reverseDictionary = [];

	public static function clearCache(): void
	{
		parent::clearCache();

		$className = get_called_class();
		if (isset(self::$dictionary[$className]))
		{
			unset(self::$dictionary[$className]);
		}
		if (isset(self::$reverseDictionary[$className]))
		{
			unset(self::$reverseDictionary[$className]);
		}
		unset($className);
	}

	protected static function getXmlIdListById(int $hlblockId, array $idList): array
	{
		$className = get_called_class();
		if (!isset(self::$dictionary[$className]))
		{
			self::$dictionary[$className] = [];
		}

		$result = [];

		$needList = [];
		foreach ($idList as $id)
		{
			if (isset(self::$dictionary[$className][$id]))
			{
				if (self::$dictionary[$className][$id] !== false)
				{
					$result[$id] = self::$dictionary[$className][$id];
				}
			}
			else
			{
				$needList[] = $id;
			}
		}
		unset($id);

		if (!empty($needList))
		{
			$list = Type\HighloadBlock::getXmlIdById($hlblockId, $needList);
			foreach ($needList as $id)
			{
				self::$dictionary[$className][$id] = $list[$id] ?? false;
				if (self::$dictionary[$className][$id] !== false)
				{
					$result[$id] = self::$dictionary[$className][$id];
				}
			}
			unset($id, $list);
		}
		unset($needList);
		unset($className);

		return $result;
	}

	protected static function getXmlIdById(int $hlblockId, int $id): ?string
	{
		$list = static::getXmlIdListById($hlblockId, [$id]);

		return $list[$id] ?? null;
	}

	protected static function getIdListByXmlId(int $hlblockId, array $xmlIdList): array
	{
		$className = get_called_class();
		if (!isset(self::$reverseDictionary[$className]))
		{
			self::$reverseDictionary[$className] = [];
		}

		$result = [];

		$needList = [];
		foreach ($xmlIdList as $xmlId)
		{
			if (isset(self::$reverseDictionary[$className][$xmlId]))
			{
				if (self::$reverseDictionary[$className][$xmlId] !== false)
				{
					$result[$xmlId] = self::$reverseDictionary[$className][$xmlId];
				}
			}
			else
			{
				$needList[] = $xmlId;
			}
		}
		unset($id);

		if (!empty($needList))
		{
			$list = Type\HighloadBlock::getIdByXmlId($hlblockId, $needList);
			foreach ($needList as $xmlId)
			{
				self::$reverseDictionary[$className][$xmlId] = $list[$xmlId] ?? false;
				if (self::$reverseDictionary[$className][$xmlId] !== false)
				{
					$result[$xmlId] = self::$reverseDictionary[$className][$xmlId];
				}
			}
			unset($xmlId);
		}
		unset($needList);
		unset($className);

		return $result;
	}

	protected static function getIdByXmlId(int $hlblockId, string $xmlId): ?int
	{
		$list = static::getIdListByXmlId($hlblockId, [$xmlId]);

		return $list[$xmlId] ?? null;
	}
}