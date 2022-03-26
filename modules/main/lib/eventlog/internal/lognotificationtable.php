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
use Bitrix\Main\ORM\Query;
use Bitrix\Main\Localization\Loc;

/**
 * Class LogNotificationTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_LogNotification_Query query()
 * @method static EO_LogNotification_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_LogNotification_Result getById($id)
 * @method static EO_LogNotification_Result getList(array $parameters = [])
 * @method static EO_LogNotification_Entity getEntity()
 * @method static \Bitrix\Main\EventLog\Internal\EO_LogNotification createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\EventLog\Internal\EO_LogNotification_Collection createCollection()
 * @method static \Bitrix\Main\EventLog\Internal\EO_LogNotification wakeUpObject($row)
 * @method static \Bitrix\Main\EventLog\Internal\EO_LogNotification_Collection wakeUpCollection($rows)
 */
class LogNotificationTable extends Data\DataManager
{
	public static function getTableName()
	{
		return 'b_log_notification';
	}

	public static function getMap()
	{
		return [
			(new Fields\IntegerField("ID"))
				->configurePrimary(true)
				->configureAutocomplete(true),
			(new Fields\BooleanField("ACTIVE"))
				->configureValues("N", "Y")
				->configureDefaultValue("Y"),
			(new Fields\StringField("NAME")),
			(new Fields\StringField("AUDIT_TYPE_ID"))
				->configureRequired(true)
				->configureTitle(Loc::getMessage("log_notification_table_audit_type")),
			(new Fields\StringField("ITEM_ID")),
			(new Fields\IntegerField("USER_ID")),
			(new Fields\StringField("REMOTE_ADDR")),
			(new Fields\StringField("USER_AGENT")),
			(new Fields\StringField("REQUEST_URI")),
			(new Fields\IntegerField("CHECK_INTERVAL")),
			(new Fields\IntegerField("ALERT_COUNT")),
			(new Fields\DatetimeField("DATE_CHECKED")),
			(new Fields\Relations\OneToMany('ACTIONS', LogNotificationActionTable::class, 'NOTIFICATION'))
				->configureJoinType(Query\Join::TYPE_LEFT)
				->configureCascadeDeletePolicy(Fields\Relations\CascadePolicy::FOLLOW),
		];
	}
}
