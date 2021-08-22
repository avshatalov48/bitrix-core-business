<?php
namespace Bitrix\Rest;

use Bitrix\Main;

/**
 * Class AppLogTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TIMESTAMP_X datetime mandatory default 'CURRENT_TIMESTAMP'
 * <li> APP_ID int mandatory
 * <li> ACTION_TYPE string(50) mandatory
 * <li> USER_ID int mandatory
 * <li> USER_ADMIN bool optional default 'Y'
 * </ul>
 *
 * @package Bitrix\Rest
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_AppLog_Query query()
 * @method static EO_AppLog_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_AppLog_Result getById($id)
 * @method static EO_AppLog_Result getList(array $parameters = array())
 * @method static EO_AppLog_Entity getEntity()
 * @method static \Bitrix\Rest\EO_AppLog createObject($setDefaultValues = true)
 * @method static \Bitrix\Rest\EO_AppLog_Collection createCollection()
 * @method static \Bitrix\Rest\EO_AppLog wakeUpObject($row)
 * @method static \Bitrix\Rest\EO_AppLog_Collection wakeUpCollection($rows)
 */

class AppLogTable extends Main\Entity\DataManager
{
	const ACTION_TYPE_ADD = 'ADD';
	const ACTION_TYPE_INSTALL = 'INSTALL';
	const ACTION_TYPE_UPDATE = 'UPDATE';
	const ACTION_TYPE_UNINSTALL = 'UNINSTALL';

	const USER_ADMIN = 'Y';
	const USER_NOT_ADMIN = 'N';

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_rest_app_log';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'TIMESTAMP_X' => array(
				'data_type' => 'datetime',
			),
			'APP_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'ACTION_TYPE' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'USER_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'USER_ADMIN' => array(
				'data_type' => 'boolean',
				'values' => array(static::USER_NOT_ADMIN, static::USER_ADMIN),
			),
		);
	}

	public static function log($appId, $action)
	{
		global $USER;

		$fields = array(
			'APP_ID' => $appId,
			'ACTION_TYPE' => $action,
			'USER_ID' => $USER->getId(),
		);

		if($USER->IsAuthorized())
		{
			$fields['USER_ADMIN'] = \CRestUtil::isAdmin() ? static::USER_ADMIN : static::USER_NOT_ADMIN;
		}
		else
		{
			$fields['USER_ADMIN'] = static::USER_NOT_ADMIN;
		}

		return static::add($fields);
	}
}