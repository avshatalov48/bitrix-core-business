<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2020 Bitrix
 */

namespace Bitrix\Iblock\ORM;

use Bitrix\Main\ORM\Objectify\EntityObject;

/**
 * @package    bitrix
 * @subpackage main
 */
class ValueStorage extends EntityObject
{
	public function setValue($value)
	{
		if ($this->entity->hasField(ValueStorageTable::GENERIC_VALUE_FIELD_NAME))
		{

			$this->set(ValueStorageTable::GENERIC_VALUE_FIELD_NAME, $value);
		}

		return $this->sysSetValue('VALUE', $value);
	}
}
