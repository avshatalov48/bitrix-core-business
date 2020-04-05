<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage iblock
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Iblock\ORM;

use Bitrix\Iblock\IblockTable;

/**
 * @package    bitrix
 * @subpackage iblock
 */
class Loader
{
	public static function autoLoad($class)
	{
		// search for data classes only
		// object and collection classes will be resolved by main orm loader
		if (substr($class, -5) !== 'Table')
		{
			return;
		}

		// check iblock regular namespace
		$namespace = substr($class, 0, strrpos($class, '\\'));
		$className = substr($class, strrpos($class, '\\') + 1);

		if (
			$namespace == IblockTable::DATA_CLASS_NAMESPACE // regular iblock entity namespace
			&& strpos($className, IblockTable::DATA_CLASS_PREFIX) === 0 // prefix of iblock entities
		)
		{
			$iblockApiCode = substr($className, strlen(IblockTable::DATA_CLASS_PREFIX), -5);
			IblockTable::compileEntity($iblockApiCode);
		}
	}
}
