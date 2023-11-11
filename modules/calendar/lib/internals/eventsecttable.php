<?php
namespace Bitrix\Calendar\Internals;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;

/**
 * Class EventSectTable
 *
 * Fields:
 * <ul>
 * <li> EVENT_ID int mandatory
 * <li> SECT_ID int mandatory
 * <li> REL string(10) optional
 * </ul>
 *
 * @package Bitrix\Calendar
 **/

class EventSectTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_calendar_event_sect';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			(new IntegerField('EVENT_ID'))
				->configurePrimary()
			,
			(new IntegerField('SECT_ID'))
				->configurePrimary()
			,
			(new StringField('REL'))
				->configureSize(10)
			,
		];
	}
}