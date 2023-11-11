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
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_ElementV1_Query query()
 * @method static EO_ElementV1_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_ElementV1_Result getById($id)
 * @method static EO_ElementV1_Result getList(array $parameters = [])
 * @method static EO_ElementV1_Entity getEntity()
 * @method static \Bitrix\Iblock\ORM\EO_ElementV1 createObject($setDefaultValues = true)
 * @method static \Bitrix\Iblock\ORM\EO_ElementV1_Collection createCollection()
 * @method static \Bitrix\Iblock\ORM\EO_ElementV1 wakeUpObject($row)
 * @method static \Bitrix\Iblock\ORM\EO_ElementV1_Collection wakeUpCollection($rows)
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
