<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage iblock
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Iblock\ORM;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Event;

/**
 * @package    bitrix
 * @subpackage iblock
 *
 * @method static ElementEntity getEntity()
 */
abstract class CommonElementTable extends DataManager
{
	public static function getEntityClass()
	{
		return ElementEntity::class;
	}

	public static function getQueryClass()
	{
		return Query::class;
	}

	public static function setDefaultScope($query)
	{
		return $query->where("IBLOCK_ID", static::getEntity()->getIblock()->getId());
	}

	public static function getTableName()
	{
		return ElementTable::getTableName();
	}

	public static function getMap()
	{
		return ElementTable::getMap();
	}

	public static function onAfterDelete(Event $event)
	{
		parent::onAfterDelete($event);

		$id = (int) end($event->getParameters()['primary']);
		$connection = static::getEntity()->getConnection();

		// delete property values
		$tables = [static::getEntity()->getSingleValueTableName(), static::getEntity()->getMultiValueTableName()];

		foreach (array_unique($tables) as $table)
		{
			$connection->query("DELETE FROM {$table} WHERE IBLOCK_ELEMENT_ID = {$id}");
		}
	}
}
