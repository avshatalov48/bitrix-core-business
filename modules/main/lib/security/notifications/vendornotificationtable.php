<?php

namespace Bitrix\Main\Security\Notifications;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;

/**
 * Class VendorNotificationTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_VendorNotification_Query query()
 * @method static EO_VendorNotification_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_VendorNotification_Result getById($id)
 * @method static EO_VendorNotification_Result getList(array $parameters = [])
 * @method static EO_VendorNotification_Entity getEntity()
 * @method static \Bitrix\Main\Security\Notifications\EO_VendorNotification createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Security\Notifications\EO_VendorNotification_Collection createCollection()
 * @method static \Bitrix\Main\Security\Notifications\EO_VendorNotification wakeUpObject($row)
 * @method static \Bitrix\Main\Security\Notifications\EO_VendorNotification_Collection wakeUpCollection($rows)
 */
class VendorNotificationTable extends DataManager
{
	public static function getTableName()
	{
		return 'b_sec_vendor_notification';
	}

	public static function getMap()
	{
		global $USER;

		return [
			(new StringField('VENDOR_ID'))
				->configurePrimary(),
			(new TextField('DATA')),
			(new ExpressionField('NOT_SIGNED', 'NOT EXISTS(
				SELECT 1 FROM b_sec_vendor_notification_sign s WHERE %s=s.NOTIFICATION_VENDOR_ID and s.USER_ID='.intval($USER->getId()).'
			)', ['VENDOR_ID']))
				->configureValueType(BooleanField::class)
		];
	}
}