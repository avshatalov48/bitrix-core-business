<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage iblock
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Iblock\ORM;


/**
 * @package    bitrix
 * @subpackage iblock
 */
class ElementV1Entity extends ElementEntity
{
	public function getSingleValueTableName()
	{
		return 'b_iblock_element_property';
	}

	public function getMultiValueTableName()
	{
		return 'b_iblock_element_property';
	}
}
