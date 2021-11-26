<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage blog
 * @copyright 2001-2018 Bitrix
 */
namespace Bitrix\Blog;

use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlException;
use Bitrix\Main\Entity;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\LogRightTable;
use Bitrix\Socialnetwork\LogTable;

Loc::loadMessages(__FILE__);

/**
 * Class PostSocnetRightsTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_PostSocnetRights_Query query()
 * @method static EO_PostSocnetRights_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_PostSocnetRights_Result getById($id)
 * @method static EO_PostSocnetRights_Result getList(array $parameters = array())
 * @method static EO_PostSocnetRights_Entity getEntity()
 * @method static \Bitrix\Blog\EO_PostSocnetRights createObject($setDefaultValues = true)
 * @method static \Bitrix\Blog\EO_PostSocnetRights_Collection createCollection()
 * @method static \Bitrix\Blog\EO_PostSocnetRights wakeUpObject($row)
 * @method static \Bitrix\Blog\EO_PostSocnetRights_Collection wakeUpCollection($rows)
 */
class PostSocnetRightsTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_blog_socnet_rights';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'POST_ID' => array(
				'data_type' => 'integer',
			),
			'POST' => array(
				'data_type' => '\Bitrix\Blog\Post',
				'reference' => array('=this.POST_ID' => 'ref.ID')
			),
			'ENTITY_TYPE' => array(
				'data_type' => 'string'
			),
			'ENTITY_ID' => array(
				'data_type' => 'integer',
			),
			'ENTITY' => array(
				'data_type' => 'string'
			),
		);

		return $fieldsMap;
	}

	public static function recalcGroupPostRights($params = array())
	{
		if (!is_array($params))
		{
			return false;
		}

		$groupId = (isset($params['groupId']) ? intval($params['groupId']) : 0);
		$newRole = (isset($params['role']) ? $params['role'] : false);

		$application = \Bitrix\Main\Application::getInstance();
		$connection = $application->getConnection();

		if (
			$groupId <= 0
			|| empty($newRole)
		)
		{
			return false;
		}

		if (!Loader::includeModule('socialnetwork'))
		{
			return false;
		}

		$queryRes = true;

		$prevValue = \Bitrix\Blog\Item\PostSocnetRights::get($groupId);
		if ($prevValue != $newRole)
		{
			$sql = "DELETE FROM ".self::getTableName()." WHERE ENTITY_TYPE = 'SG' AND ENTITY_ID = ".$groupId;
			try
			{
				$connection->query($sql);
			}
			catch (SqlException $e)
			{
				$queryRes = false;
			}

			if ($queryRes)
			{
				$rightsList = \CBlogPost::getFullGroupRoleSet($newRole, "SG".$groupId."_");
				$rightsList[] = 'SG'.$groupId;
				$rightsList = array_unique($rightsList);

				foreach($rightsList as $right)
				{
					if (!$queryRes)
					{
						break;
					}

					$sql = "INSERT INTO ".self::getTableName()." (POST_ID, ENTITY_TYPE, ENTITY_ID, ENTITY) ".
						"SELECT SL.SOURCE_ID, 'SG', ".$groupId.", '".$right."' ".
						"FROM ".LogTable::getTableName()." SL ".
						"INNER JOIN ".LogRightTable::getTableName()." SLR ON SLR.LOG_ID = SL.ID AND SLR.GROUP_CODE = 'SG".$groupId."' ".
						"WHERE SL.EVENT_ID IN ('".implode("', '", \Bitrix\Blog\Integration\Socialnetwork\Log::getEventIdList())."')";

					try
					{
						$connection->query($sql);
					}
					catch (SqlException $e)
					{
						$queryRes = false;
					}
				}

				if ($queryRes)
				{
					$sql = "DELETE ".LogRightTable::getTableName()." ".
						"FROM ".LogRightTable::getTableName()." ".
						"INNER JOIN ".LogTable::getTableName()." ON ".LogTable::getTableName().".ID = ".LogRightTable::getTableName().".LOG_ID AND ".LogTable::getTableName().".EVENT_ID IN ('".implode("', '", \Bitrix\Blog\Integration\Socialnetwork\Log::getEventIdList())."') ".
						"WHERE GROUP_CODE LIKE 'SG".$groupId."%'";

					try
					{
						$connection->query($sql);
					}
					catch (SqlException $e)
					{
						$queryRes = false;
					}
				}

				if ($queryRes)
				{
					$sql = "DELETE ".LogRightTable::getTableName()." ".
						"FROM ".LogRightTable::getTableName()." ".
						"INNER JOIN ".LogTable::getTableName()." ON ".LogTable::getTableName().".ID = ".LogRightTable::getTableName().".LOG_ID AND ".LogTable::getTableName().".EVENT_ID IN ('".implode("', '", \Bitrix\Blog\Integration\Socialnetwork\Log::getEventIdList())."') ".
						"WHERE GROUP_CODE LIKE 'OSG".$groupId."%'";

					try
					{
						$connection->query($sql);
					}
					catch (SqlException $e)
					{
						$queryRes = false;
					}
				}

				if ($queryRes)
				{
					$sql = "INSERT INTO ".LogRightTable::getTableName()." (LOG_ID, GROUP_CODE, LOG_UPDATE) ".
						"SELECT SL.ID, BSR.ENTITY, SL.LOG_UPDATE ".
						"FROM ".LogTable::getTableName()." SL ".
						"INNER JOIN ".self::getTableName()." BSR ON BSR.POST_ID = SL.SOURCE_ID AND (BSR.ENTITY LIKE 'SG".$groupId."%' OR BSR.ENTITY LIKE 'OSG".$groupId."%') ".
						"WHERE SL.EVENT_ID IN ('".implode("', '", \Bitrix\Blog\Integration\Socialnetwork\Log::getEventIdList())."')";

					try
					{
						$connection->query($sql);
					}
					catch (SqlException $e)
					{
						$queryRes = false;
					}
				}

				BXClearCache(true, "/blog/getsocnetperms");
			}
		}

		return $queryRes;
	}

	public static function deleteByEntity($value = '')
	{
		if ($value == '')
		{
			return false;
		}

		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();

		$tableName = self::getTableName();
		$connection->queryExecute("DELETE FROM {$tableName} WHERE `ENTITY` = '".$helper->forSql($value)."'");

		return true;
	}
}
