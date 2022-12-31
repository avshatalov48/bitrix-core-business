<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\NotImplementedException;

/**
 * Class WorkgroupFavoritesTable
 *
 * Fields:
 * <ul>
 * <li> GROUP_ID int mandatory
 * <li> GROUP reference to {@link \Bitrix\Socialnetwork\WorkgroupTable}
 * <li> USER_ID int mandatory
 * <li> USER reference to {@link \Bitrix\Main\UserTable}
 * <li> DATE_ADD datetime
 * </ul>
 *
 * @package Bitrix\Socialnetwork
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_WorkgroupFavorites_Query query()
 * @method static EO_WorkgroupFavorites_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_WorkgroupFavorites_Result getById($id)
 * @method static EO_WorkgroupFavorites_Result getList(array $parameters = [])
 * @method static EO_WorkgroupFavorites_Entity getEntity()
 * @method static \Bitrix\Socialnetwork\EO_WorkgroupFavorites createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\EO_WorkgroupFavorites_Collection createCollection()
 * @method static \Bitrix\Socialnetwork\EO_WorkgroupFavorites wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\EO_WorkgroupFavorites_Collection wakeUpCollection($rows)
 */
class WorkgroupFavoritesTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sonet_group_favorites';
	}

	/**
	 * Returns entity map definition.
	 * @return array
	 */
	public static function getMap()
	{
		$fieldsMap = array(
			'USER_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'USER' => array(
				'data_type' => '\Bitrix\Main\User',
				'reference' => array('=this.USER_ID' => 'ref.ID')
			),
			'GROUP_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'GROUP' => array(
				'data_type' => '\Bitrix\Socialnetwork\Workgroup',
				'reference' => array('=this.GROUP_ID' => 'ref.ID')
			),
			'DATE_ADD' => array(
				'data_type' => 'datetime'
			),
		);

		return $fieldsMap;
	}

	/**
	 * Adds a workgroup GROUP_ID to a favorites list of a user USER_ID
	 * @param array $params.
	 * @return bool
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function set($params = array())
	{
		global $USER, $CACHE_MANAGER;
		$res = false;

		if (
			!is_array($params)
			|| !isset($params['GROUP_ID'])
			|| intval($params['GROUP_ID']) <= 0
		)
		{
			throw new Main\SystemException("Empty groupId.");
		}

		$groupId = intval($params['GROUP_ID']);

		$userId = (
			isset($params['USER_ID'])
			&& intval($params['USER_ID']) > 0
				? intval($params['USER_ID'])
				: $USER->getId()
		);

		if (intval($userId) <= 0)
		{
			throw new Main\SystemException("Empty userId.");
		}

		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$insertFields = array(
			"USER_ID" => $userId,
			"GROUP_ID" => $groupId,
			"DATE_ADD" => new \Bitrix\Main\DB\SqlExpression($helper->getCurrentDateTimeFunction()),
		);

		$updateFields = array(
			"DATE_ADD" => new \Bitrix\Main\DB\SqlExpression($helper->getCurrentDateTimeFunction()),
		);

		$merge = $helper->prepareMerge(
			static::getTableName(),
			array("USER_ID", "GROUP_ID"),
			$insertFields,
			$updateFields
		);

		if ($merge[0] != "")
		{
			$res = $connection->query($merge[0]);
		}

		if(
			$res
			&& defined("BX_COMP_MANAGED_CACHE")
		)
		{
			$CACHE_MANAGER->clearByTag("sonet_group_favorites_U".$userId);
		}

		return $res;
	}

	public static function add(array $data)
	{
		throw new NotImplementedException("Use set() method of the class.");
	}

	public static function update($primary, array $data)
	{
		throw new NotImplementedException("Use set() method of the class.");
	}
}
