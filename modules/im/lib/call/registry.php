<?php

namespace Bitrix\Im\Call;

use Bitrix\Im\Model\CallTable;
use Bitrix\Im\V2\Call\CallFactory;

class Registry
{
	/** @var Call[] */
	protected static array $calls = [];

	/**
	 * @param int $id Id of the call
	 * @return Call|null
	 */
	public static function getCallWithId(int $id): ?Call
	{
		if (static::$calls[$id] instanceof Call)
		{
			return static::$calls[$id];
		}

		$row = CallTable::getRowById($id);
		if (!$row)
		{
			return null;
		}

		static::$calls[$id] = CallFactory::createWithArray($row['PROVIDER'], $row);

		return static::$calls[$id];
	}

	public static function getCallWithPublicId($publicId)
	{

	}

	public static function getCallWithEntity($entityType, $entityId)
	{

	}
}