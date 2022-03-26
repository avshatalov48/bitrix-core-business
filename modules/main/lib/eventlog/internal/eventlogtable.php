<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2019 Bitrix
 */

namespace Bitrix\Main\EventLog\Internal;

use Bitrix\Main\ORM\Data;
use Bitrix\Main\ORM\Fields;

/**
 * Class EventLogTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_EventLog_Query query()
 * @method static EO_EventLog_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_EventLog_Result getById($id)
 * @method static EO_EventLog_Result getList(array $parameters = [])
 * @method static EO_EventLog_Entity getEntity()
 * @method static \Bitrix\Main\EventLog\Internal\EO_EventLog createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\EventLog\Internal\EO_EventLog_Collection createCollection()
 * @method static \Bitrix\Main\EventLog\Internal\EO_EventLog wakeUpObject($row)
 * @method static \Bitrix\Main\EventLog\Internal\EO_EventLog_Collection wakeUpCollection($rows)
 */
class EventLogTable extends Data\DataManager
{
	public static function getTableName()
	{
		return 'b_event_log';
	}

	public static function getMap()
	{
		return [
			(new Fields\IntegerField("ID"))
				->configurePrimary(true)
				->configureAutocomplete(true),
			(new Fields\DatetimeField("TIMESTAMP_X")),
			(new Fields\StringField("SEVERITY")),
			(new Fields\StringField("AUDIT_TYPE_ID")),
			(new Fields\StringField("MODULE_ID")),
			(new Fields\StringField("ITEM_ID")),
			(new Fields\StringField("REMOTE_ADDR")),
			(new Fields\TextField("USER_AGENT")),
			(new Fields\TextField("REQUEST_URI")),
			(new Fields\StringField("SITE_ID")),
			(new Fields\IntegerField("USER_ID")),
			(new Fields\IntegerField("GUEST_ID")),
			(new Fields\TextField("DESCRIPTION")),
		];
	}
}
