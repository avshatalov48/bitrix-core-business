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
 * Class WorkgroupViewTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_WorkgroupView_Query query()
 * @method static EO_WorkgroupView_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_WorkgroupView_Result getById($id)
 * @method static EO_WorkgroupView_Result getList(array $parameters = [])
 * @method static EO_WorkgroupView_Entity getEntity()
 * @method static \Bitrix\Socialnetwork\EO_WorkgroupView createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\EO_WorkgroupView_Collection createCollection()
 * @method static \Bitrix\Socialnetwork\EO_WorkgroupView wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\EO_WorkgroupView_Collection wakeUpCollection($rows)
 */
class WorkgroupViewTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sonet_group_view';
	}

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
			'DATE_VIEW' => array(
				'data_type' => 'datetime'
			),
		);

		return $fieldsMap;
	}

	public static function set($params = array())
	{
		global $USER, $CACHE_MANAGER;

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
			"DATE_VIEW" => new \Bitrix\Main\DB\SqlExpression($helper->getCurrentDateTimeFunction()),
		);

		$updateFields = array(
			"DATE_VIEW" => new \Bitrix\Main\DB\SqlExpression($helper->getCurrentDateTimeFunction()),
		);

		$merge = $helper->prepareMerge(
			static::getTableName(),
			array("USER_ID", "GROUP_ID"),
			$insertFields,
			$updateFields
		);

		if ($merge[0] != "")
		{
			$connection->query($merge[0]);
		}

		if(defined("BX_COMP_MANAGED_CACHE"))
		{
			$CACHE_MANAGER->ClearByTag("sonet_group_view_U".$userId);
		}
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
