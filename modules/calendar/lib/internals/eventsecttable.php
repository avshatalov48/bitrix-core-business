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
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_EventSect_Query query()
 * @method static EO_EventSect_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_EventSect_Result getById($id)
 * @method static EO_EventSect_Result getList(array $parameters = [])
 * @method static EO_EventSect_Entity getEntity()
 * @method static \Bitrix\Calendar\Internals\EO_EventSect createObject($setDefaultValues = true)
 * @method static \Bitrix\Calendar\Internals\EO_EventSect_Collection createCollection()
 * @method static \Bitrix\Calendar\Internals\EO_EventSect wakeUpObject($row)
 * @method static \Bitrix\Calendar\Internals\EO_EventSect_Collection wakeUpCollection($rows)
 */

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