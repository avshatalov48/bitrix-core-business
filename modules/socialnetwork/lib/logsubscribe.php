<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main\Application;
use Bitrix\Main\Entity;

/**
 * Class LogSubscribeTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_LogSubscribe_Query query()
 * @method static EO_LogSubscribe_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_LogSubscribe_Result getById($id)
 * @method static EO_LogSubscribe_Result getList(array $parameters = [])
 * @method static EO_LogSubscribe_Entity getEntity()
 * @method static \Bitrix\Socialnetwork\EO_LogSubscribe createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\EO_LogSubscribe_Collection createCollection()
 * @method static \Bitrix\Socialnetwork\EO_LogSubscribe wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\EO_LogSubscribe_Collection wakeUpCollection($rows)
 */
class LogSubscribeTable extends Entity\DataManager
{
	const TYPE_COUNTER_COMMENT_PUSH = 'CCP';
	const TTL = 1209600; //60*60*24*14

	private static function getTypes()
	{
		return array(
			self::TYPE_COUNTER_COMMENT_PUSH
		);
	}

	public static function getTableName()
	{
		return 'b_sonet_log_subscribe';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'USER_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'LOG_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'TYPE' => array(
				'data_type' => 'string',
				'primary' => true
			),
			'END_DATE' => array(
				'data_type' => 'datetime',
			),
		);

		return $fieldsMap;
	}

	public static function set($params = array())
	{
		$userId = (isset($params['userId']) ? intval($params['userId']) : 0);
		$logId = (isset($params['logId']) ? intval($params['logId']) : 0);
		$type = (isset($params['type']) ? $params['type'] : '');

		if (
			$userId <= 0
			|| $logId <= 0
		)
		{
			return false;
		}

		if (is_array($type))
		{
			$typeList = $type;
			foreach($typeList as $type)
			{
				$params['type'] = $type;
				self::set($params);
			}
			return true;
		}

		if (!in_array($type, self::getTypes()))
		{
			return false;
		}

		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();

		$insertFields = array(
			"USER_ID" => $userId,
			"LOG_ID" => $logId,
			"TYPE" => $helper->forSql($type),
		);

		$updateFields = array(
		);

		if (
			isset($params['endDate'])
			&& $params['endDate'] instanceof \Bitrix\Main\Type\DateTime
		)
		{
			$insertFields["END_DATE"] = $params['endDate'];
			$updateFields["END_DATE"] = $params['endDate'];
		}
		elseif (!empty($params['ttl']))
		{
			$endDate = \Bitrix\Main\Type\DateTime::createFromTimestamp(time() + self::TTL);
			$insertFields["END_DATE"] = $endDate;
			$updateFields["END_DATE"] = $endDate;
		}
		else
		{
			$updateFields["END_DATE"] = false;
		}

		$merge = $helper->prepareMerge(
			static::getTableName(),
			array("USER_ID", "LOG_ID", "TYPE"),
			$insertFields,
			$updateFields
		);

		if ($merge[0] != "")
		{
			$connection->query($merge[0]);
		}

		return true;
	}
}
