<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage iblock
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Iblock\ORM;

use Bitrix\Main\ORM\Data\DataManager;

/**
 * @package    bitrix
 * @subpackage iblock
 */
abstract class ValueStorageTable extends DataManager
{
	const GENERIC_VALUE_FIELD_NAME = 'IBLOCK_GENERIC_VALUE';

	public static function getEntityClass()
	{
		return ValueStorageEntity::class;
	}

	public static function getObjectParentClass()
	{
		return ValueStorage::class;
	}
}
