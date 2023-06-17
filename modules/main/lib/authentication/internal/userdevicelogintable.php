<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Authentication\Internal;

use Bitrix\Main;
use Bitrix\Main\ORM\Data;
use Bitrix\Main\ORM\Fields;

/**
 * Class UserDeviceLoginTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_UserDeviceLogin_Query query()
 * @method static EO_UserDeviceLogin_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_UserDeviceLogin_Result getById($id)
 * @method static EO_UserDeviceLogin_Result getList(array $parameters = [])
 * @method static EO_UserDeviceLogin_Entity getEntity()
 * @method static \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin_Collection createCollection()
 * @method static \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin wakeUpObject($row)
 * @method static \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin_Collection wakeUpCollection($rows)
 */
class UserDeviceLoginTable extends Data\DataManager
{
	use Data\Internal\DeleteByFilterTrait;

	public static function getTableName()
	{
		return 'b_user_device_login';
	}

	public static function getMap()
	{
		return [
			(new Fields\IntegerField('ID'))
				->configurePrimary(true)
				->configureAutocomplete(true),

			(new Fields\IntegerField('DEVICE_ID'))
				->addValidator(new Fields\Validators\ForeignValidator(UserDeviceTable::getEntity()->getField('ID'))),

			(new Fields\DatetimeField('LOGIN_DATE')),

			(new Fields\StringField('IP')),

			(new Fields\IntegerField('CITY_GEOID'))
				->configureNullable(),

			(new Fields\IntegerField('REGION_GEOID'))
				->configureNullable(),

			(new Fields\StringField('COUNTRY_ISO_CODE'))
				->configureNullable(),

			(new Fields\IntegerField('APP_PASSWORD_ID'))
				->configureNullable(),

			(new Fields\IntegerField('STORED_AUTH_ID'))
				->configureNullable(),

			(new Fields\IntegerField('HIT_AUTH_ID'))
				->configureNullable(),
		];
	}

	public static function deleteByDeviceFilter($where)
	{
		if($where == '')
		{
			throw new Main\ArgumentException("Deleting by empty filter is not allowed, use truncate (b_user_device_login).", "where");
		}

		$entity = static::getEntity();
		$conn = $entity->getConnection();

		$conn->query("
			DELETE DL FROM b_user_device_login DL 
			WHERE DL.DEVICE_ID IN(
				SELECT ID FROM b_user_device 
				{$where} 
			)"
		);

		$entity->cleanCache();
	}
}
