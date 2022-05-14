<?php


namespace Bitrix\Calendar\Internals;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity;

/**
 * Class EventTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Event_Query query()
 * @method static EO_Event_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Event_Result getById($id)
 * @method static EO_Event_Result getList(array $parameters = array())
 * @method static EO_Event_Entity getEntity()
 * @method static \Bitrix\Calendar\Internals\EO_Event createObject($setDefaultValues = true)
 * @method static \Bitrix\Calendar\Internals\EO_Event_Collection createCollection()
 * @method static \Bitrix\Calendar\Internals\EO_Event wakeUpObject($row)
 * @method static \Bitrix\Calendar\Internals\EO_Event_Collection wakeUpCollection($rows)
 */
class CalendarLogTable extends Main\Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_calendar_log';
	}

	/**
	 * @return array
	 * @throws Main\SystemException
	 */
	public static function getMap()
	{
		return [
			new Entity\IntegerField('ID', [
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('SYNC_DEBUG_LOG_ENTITY_ID_FIELD'),
			]),
			new Entity\DatetimeField('TIMESTAMP_X', [
				'title' => Loc::getMessage('LOG_ENTITY_TIMESTAMP_X_FIELD'),
			]),
			new Entity\TextField('MESSAGE', [
				'title' => Loc::getMessage('CALENDAR_LOG_ENTITY_MESSAGE_FIELD'),
			]),
		];
	}
}