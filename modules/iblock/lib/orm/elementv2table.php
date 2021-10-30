<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage iblock
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Iblock\ORM;

use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\Objectify\Collection;
use Bitrix\Main\ORM\Objectify\EntityObject;

/**
 * @package    bitrix
 * @subpackage iblock
 *
 * @method static ElementV2Entity getEntity()
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_ElementV2_Query query()
 * @method static EO_ElementV2_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_ElementV2_Result getById($id)
 * @method static EO_ElementV2_Result getList(array $parameters = array())
 * @method static EO_ElementV2_Entity getEntity()
 * @method static \Bitrix\Iblock\ORM\EO_ElementV2 createObject($setDefaultValues = true)
 * @method static \Bitrix\Iblock\ORM\EO_ElementV2_Collection createCollection()
 * @method static \Bitrix\Iblock\ORM\EO_ElementV2 wakeUpObject($row)
 * @method static \Bitrix\Iblock\ORM\EO_ElementV2_Collection wakeUpCollection($rows)
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

	public static function onAfterAdd(Event $event)
	{
		parent::onAfterAdd($event);

		$id = $event->getParameter('id');
		$table = static::getEntity()->getSingleValueTableName();
		$connection = static::getEntity()->getConnection();

		// create single value row
		$connection->add($table, ['IBLOCK_ELEMENT_ID' => $id]);
	}
}
