<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Main;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class UserGroupTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_UserGroup_Query query()
 * @method static EO_UserGroup_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_UserGroup_Result getById($id)
 * @method static EO_UserGroup_Result getList(array $parameters = [])
 * @method static EO_UserGroup_Entity getEntity()
 * @method static \Bitrix\Main\EO_UserGroup createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\EO_UserGroup_Collection createCollection()
 * @method static \Bitrix\Main\EO_UserGroup wakeUpObject($row)
 * @method static \Bitrix\Main\EO_UserGroup_Collection wakeUpCollection($rows)
 */
class UserGroupTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_user_group';
	}

	public static function getMap()
	{
		return array(
			'USER_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'USER' => array(
				'data_type' => 'User',
				'reference' => array('=this.USER_ID' => 'ref.ID')
			),
			'GROUP_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'GROUP' => array(
				'data_type' => 'Group',
				'reference' => array('=this.GROUP_ID' => 'ref.ID')
			),
			'DATE_ACTIVE_FROM' => array(
				'data_type' => 'datetime',
			),
			'DATE_ACTIVE_TO' => array(
				'data_type' => 'datetime',
			),
		);
	}
}
