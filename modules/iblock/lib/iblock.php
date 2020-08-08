<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage iblock
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Iblock;

use Bitrix\Iblock\ORM\CommonElementTable;

/**
 * @package    bitrix
 * @subpackage iblock
 */
class Iblock extends EO_Iblock
{
	public function getEntityDataClassName()
	{
		$code = $this->fillApiCode();

		if($code <> '')
		{
			return IblockTable::DATA_CLASS_PREFIX.ucfirst($code).'Table';
		}
	}

	/**
	 * @return CommonElementTable|string
	 */
	public function getEntityDataClass()
	{
		$className = $this->getEntityDataClassName();

		if($className <> '')
		{
			return '\\'.IblockTable::DATA_CLASS_NAMESPACE.'\\'.$className;
		}

		trigger_error('API_CODE required for DataClass of iblock #'.$this->getId(), E_USER_WARNING);
	}
}
