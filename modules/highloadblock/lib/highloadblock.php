<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage highloadblock
 * @copyright  2001-2020 Bitrix
 */

namespace Bitrix\Highloadblock;

class HighloadBlock extends \Bitrix\Highloadblock\EO_HighloadBlock
{
	public function getEntityDataClass()
	{
		$entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($this->getId());
		return $entity->getDataClass();
	}
}