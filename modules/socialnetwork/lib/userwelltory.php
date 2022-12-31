<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main\Entity;
use Bitrix\Main\NotImplementedException;

/**
 * Class UserWelltoryTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_UserWelltory_Query query()
 * @method static EO_UserWelltory_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_UserWelltory_Result getById($id)
 * @method static EO_UserWelltory_Result getList(array $parameters = [])
 * @method static EO_UserWelltory_Entity getEntity()
 * @method static \Bitrix\Socialnetwork\EO_UserWelltory createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\EO_UserWelltory_Collection createCollection()
 * @method static \Bitrix\Socialnetwork\EO_UserWelltory wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\EO_UserWelltory_Collection wakeUpCollection($rows)
 */
class UserWelltoryTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sonet_user_welltory';
	}

	/**
	 * Returns entity map definition
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'USER_ID' => array(
				'data_type' => 'integer',
			),
			'USER' => array(
				'data_type' => 'Bitrix\Main\UserTable',
				'reference' => array('=this.USER_ID' => 'ref.ID'),
			),
			'STRESS' => array(
				'data_type' => 'integer',
			),
			'STRESS_TYPE' => array(
				'data_type' => 'string',
			),
			'STRESS_COMMENT' => array(
				'data_type' => 'string',
			),
			'DATE_MEASURE' => array(
				'data_type' => 'datetime'
			),
			'HASH' => array(
				'data_type' => 'string',
			),
		);
	}
}
