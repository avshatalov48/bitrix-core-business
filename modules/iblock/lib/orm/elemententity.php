<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage iblock
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Iblock\ORM;

use Bitrix\Iblock\EO_Iblock;
use Bitrix\Iblock\Iblock;
use Bitrix\Main\ORM\Entity;


/**
 * @package    bitrix
 * @subpackage iblock
 */
abstract class ElementEntity extends Entity
{
	/** @var Iblock */
	protected $iblock;

	/**
	 * @param EO_Iblock $iblock
	 */
	public function setIblock($iblock)
	{
		$this->iblock = $iblock;
	}

	/**
	 * @return EO_Iblock
	 */
	public function getIblock()
	{
		return $this->iblock;
	}

	abstract public function getSingleValueTableName();

	abstract public function getMultiValueTableName();
}
