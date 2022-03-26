<?php

namespace Bitrix\Main;

use Bitrix\Main\Entity;

/**
 * Class UserAccessTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_UserAccess_Query query()
 * @method static EO_UserAccess_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_UserAccess_Result getById($id)
 * @method static EO_UserAccess_Result getList(array $parameters = [])
 * @method static EO_UserAccess_Entity getEntity()
 * @method static \Bitrix\Main\EO_UserAccess createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\EO_UserAccess_Collection createCollection()
 * @method static \Bitrix\Main\EO_UserAccess wakeUpObject($row)
 * @method static \Bitrix\Main\EO_UserAccess_Collection wakeUpCollection($rows)
 */
class UserAccessTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_user_access';
	}

	public static function getMap()
	{
		return array(
			'USER_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'PROVIDER_ID' => array(
				'data_type' => 'string',
				'primary' => true,
				'validation' => array(__CLASS__, 'validateProviderId'),
			),
			'ACCESS_CODE' => array(
				'data_type' => 'string',
				'primary' => true,
				'validation' => array(__CLASS__, 'validateAccessCode'),
			),
		);
	}

	public static function validateProviderId()
	{
		return array(
			new Entity\Validator\Length(null, 50),
		);
	}

	public static function validateAccessCode()
	{
		return array(
			new Entity\Validator\Length(null, 100),
		);
	}
}