<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Authentication\Internal;

use Bitrix\Main\ORM\Data;
use Bitrix\Main\ORM\Fields;

/**
 * Class UserStoredAuthTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_UserStoredAuth_Query query()
 * @method static EO_UserStoredAuth_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_UserStoredAuth_Result getById($id)
 * @method static EO_UserStoredAuth_Result getList(array $parameters = [])
 * @method static EO_UserStoredAuth_Entity getEntity()
 * @method static \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth_Collection createCollection()
 * @method static \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth wakeUpObject($row)
 * @method static \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth_Collection wakeUpCollection($rows)
 */
class UserStoredAuthTable extends Data\DataManager
{
	use Data\Internal\DeleteByFilterTrait;

	public static function getTableName()
	{
		return 'b_user_stored_auth';
	}

	public static function getMap()
	{
		return [
			(new Fields\IntegerField('ID'))
				->configurePrimary(true)
				->configureAutocomplete(true),

			(new Fields\IntegerField('USER_ID')),

			(new Fields\DatetimeField('DATE_REG')),

			(new Fields\DatetimeField('LAST_AUTH')),

			(new Fields\StringField('STORED_HASH')),

			(new Fields\BooleanField('TEMP_HASH'))
				->configureValues('N', 'Y')
				->configureDefaultValue('N'),

			(new Fields\IntegerField('IP_ADDR')),
		];
	}
}
