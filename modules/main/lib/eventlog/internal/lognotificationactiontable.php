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
 * Class LogNotificationActionTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_LogNotificationAction_Query query()
 * @method static EO_LogNotificationAction_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_LogNotificationAction_Result getById($id)
 * @method static EO_LogNotificationAction_Result getList(array $parameters = [])
 * @method static EO_LogNotificationAction_Entity getEntity()
 * @method static \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction_Collection createCollection()
 * @method static \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction wakeUpObject($row)
 * @method static \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction_Collection wakeUpCollection($rows)
 */
class LogNotificationActionTable extends Data\DataManager
{
	public static function getTableName()
	{
		return 'b_log_notification_action';
	}

	public static function getMap()
	{
		return [
			(new Fields\IntegerField("ID"))
				->configurePrimary(true)
				->configureAutocomplete(true),
			(new Fields\IntegerField("NOTIFICATION_ID")),
			(new Fields\StringField("NOTIFICATION_TYPE"))
				->configureRequired(true)
				->configureTitle(Loc::getMessage("log_notification_action_type")),
			(new Fields\StringField("RECIPIENT")),
			(new Fields\TextField("ADDITIONAL_TEXT")),
			(new Fields\Relations\Reference(
				'NOTIFICATION',
				LogNotificationTable::class,
				Query\Join::on('this.NOTIFICATION_ID', 'ref.ID')
			))
				->configureJoinType(Query\Join::TYPE_INNER),
		];
	}
}
