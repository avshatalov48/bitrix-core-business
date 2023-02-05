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
 * @method static EO_CalendarLog_Query query()
 * @method static EO_CalendarLog_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_CalendarLog_Result getById($id)
 * @method static EO_CalendarLog_Result getList(array $parameters = [])
 * @method static EO_CalendarLog_Entity getEntity()
 * @method static \Bitrix\Calendar\Internals\EO_CalendarLog createObject($setDefaultValues = true)
 * @method static \Bitrix\Calendar\Internals\EO_CalendarLog_Collection createCollection()
 * @method static \Bitrix\Calendar\Internals\EO_CalendarLog wakeUpObject($row)
 * @method static \Bitrix\Calendar\Internals\EO_CalendarLog_Collection wakeUpCollection($rows)
 */
class CalendarLogTable extends Main\Entity\DataManager
{
	/**
	 * @return string
	 */
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
			new Entity\TextField('UUID', [
				'title' => Loc::getMessage('CALENDAR_LOG_ENTITY_UUID_FIELD'),
			]),
			new Entity\TextField('TYPE', [
				'title' => Loc::getMessage('CALENDAR_LOG_ENTITY_TYPE_FIELD'),
			]),
			new Entity\IntegerField('USER_ID', [
				'title' => Loc::getMessage('CALENDAR_LOG_ENTITY_USER_ID_FIELD'),
			])
		];
	}
}