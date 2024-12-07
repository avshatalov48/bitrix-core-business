<?php

namespace Bitrix\Main\Security\Notifications;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\Type\DateTime;

/**
 * Class VendorNotificationSignTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_VendorNotificationSign_Query query()
 * @method static EO_VendorNotificationSign_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_VendorNotificationSign_Result getById($id)
 * @method static EO_VendorNotificationSign_Result getList(array $parameters = [])
 * @method static EO_VendorNotificationSign_Entity getEntity()
 * @method static \Bitrix\Main\Security\Notifications\EO_VendorNotificationSign createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Security\Notifications\EO_VendorNotificationSign_Collection createCollection()
 * @method static \Bitrix\Main\Security\Notifications\EO_VendorNotificationSign wakeUpObject($row)
 * @method static \Bitrix\Main\Security\Notifications\EO_VendorNotificationSign_Collection wakeUpCollection($rows)
 */
class VendorNotificationSignTable extends DataManager
{
	public static function getTableName()
	{
		return 'b_sec_vendor_notification_sign';
	}

	public static function getMap()
	{
		return [
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete(),
			(new IntegerField('USER_ID')),
			(new StringField('NOTIFICATION_VENDOR_ID')),
			(new DatetimeField('DATE'))
		];
	}

	public static function signOrIgnore($notificationId, $userId)
	{
		$tableName = static::getTableName();
		$connection = static::getEntity()->getConnection();
		$sqlHelper = $connection->getSqlHelper();

		[$fields, $values] = $sqlHelper->prepareInsert($tableName, [
			'USER_ID' => $userId,
			'NOTIFICATION_VENDOR_ID' => $notificationId,
			'DATE' => new DateTime
		]);

		$query = $sqlHelper->getInsertIgnore($tableName, "($fields)", "VALUES($values)");

		$connection->query($query);
	}
}