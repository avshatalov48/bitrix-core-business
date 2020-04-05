<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Main;

use Bitrix\Main\Entity;

class UserAuthActionTable extends Entity\DataManager
{
	const PRIORITY_HIGH = 100;
	const PRIORITY_LOW = 200;

	const ACTION_LOGOUT = 'logout';
	const ACTION_UPDATE = 'update';

	public static function getTableName()
	{
		return 'b_user_auth_action';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'USER_ID' => array(
				'data_type' => 'integer'
			),
			'PRIORITY' => array(
				'data_type' => 'integer'
			),
			'ACTION' => array(
				'data_type' => 'string'
			),
			'ACTION_DATE' => array(
				'data_type' => 'datetime'
			),
		);
	}

	/**
	 * @param array $filter
	 */
	public static function deleteByFilter(array $filter)
	{
		$entity = static::getEntity();
		$conn = $entity->getConnection();

		$where = Entity\Query::buildFilterSql($entity, $filter);

		if($where <> '')
		{
			$where = " WHERE ".$where;
		}

		$conn->queryExecute("delete from b_user_auth_action".$where);
		$entity->cleanCache();
	}
}
