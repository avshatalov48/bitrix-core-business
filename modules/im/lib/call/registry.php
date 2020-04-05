<?php

namespace Bitrix\Im\Call;

use Bitrix\Im\Model\CallTable;

class Registry
{
	/** @var Call[] */
	protected static $calls = array();

	/**
	 *
	 * @param int $id Id of the call
	 * @return Call|false
	 */
	public static function getCallWithId($id)
	{
		if(static::$calls[$id])
			return static::$calls[$id];

		$row = CallTable::getRowById($id);
		if(!$row)
			return false;

		static::$calls[$id] = Call::createWithArray($row);
		return static::$calls[$id];
	}

	public static function getCallWithPublicId($publicId)
	{

	}

	public static function getCallWithEntity($entityType, $entityId)
	{

	}
}