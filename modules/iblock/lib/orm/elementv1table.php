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
 * @method static ElementV1Entity getEntity()
 */
class ElementV1Table extends CommonElementTable
{
	public static function getEntityClass()
	{
		return ElementV1Entity::class;
	}

	public static function getObjectParentClass()
	{
		return ElementV1::class;
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
