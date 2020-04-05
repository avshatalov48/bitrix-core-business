<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage iblock
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Iblock\ORM;

use Bitrix\Main\ORM\Objectify\Collection;
use Bitrix\Main\ORM\Objectify\EntityObject;

/**
 * @package    bitrix
 * @subpackage iblock
 *
 * @method static ElementV2Entity getEntity()
 */
class ElementV2Table extends CommonElementTable
{
	public static function getEntityClass()
	{
		return ElementV2Entity::class;
	}

	public static function getObjectParentClass()
	{
		return ElementV2::class;
	}

	/**
	 * Protection from ElementTable classes
	 * @return EntityObject|string
	 */
	public static function getObjectClass()
	{
		return static::getObjectClassByDataClass(get_called_class());
	}

	/**
	 * Protection from ElementTable classes
	 * @return Collection|string
	 */
	public static function getCollectionClass()
	{
		return static::getCollectionClassByDataClass(get_called_class());
	}
}
