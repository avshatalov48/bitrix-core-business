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
class ElementV2Entity extends ElementEntity
{
	public function getSingleValueTableName()
	{
		return "b_iblock_element_prop_s{$this->iblock->getId()}";
	}

	public function getMultiValueTableName()
	{
		return "b_iblock_element_prop_m{$this->iblock->getId()}";
	}
}
