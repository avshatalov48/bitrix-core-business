<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Socialnetwork\Item;

use Bitrix\Main\DB\SqlExpression;
use Bitrix\Socialnetwork\WorkgroupTemplateTable;
use Bitrix\Main\DB\SqlQueryException;

class WorkgroupTemplate
{
	public static function create($params = array())
	{
		global $USER;

		$result = false;

		if (
			empty($params)
			|| !is_array($params)
			|| empty($params['name'])
		)
		{
			return $result;
		}

		if (
			(
				empty($params['userId'])
				|| intval($params['userId']) <= 0
			)
			&& isset($USER)
		)
		{
			$params['userId'] = intval($USER->getId());
		}

		if (intval($params['userId']) <= 0)
		{
			return $result;
		}

		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$nowDate = new SqlExpression($helper->getCurrentDateTimeFunction());

		$result = true;

		try
		{
			$insertFields = array(
				"USER_ID" => intval($params['userId']),
				"NAME" => (!empty($params['name']) ? $params['name'] : ''),
				"OWNER_ID" => (!empty($params['ownerId']) ? intval($params['ownerId']) : 0),
				"TYPE" => (!empty($params['type']) ? $params['type'] : ''),
				"DATE_CREATE" => $nowDate,
				"PARAMS" => (is_array($params['params']) && !empty($params['params']) ? serialize($params['params']) : '')
			);

			$tableName = WorkgroupTemplateTable::getTableName();
			list($prefix, $values) = $helper->prepareInsert($tableName, $insertFields);

			$connection->queryExecute("INSERT INTO {$tableName} ({$prefix}) VALUES ({$values})");

		}
		catch(SqlQueryException $exception)
		{
			$result = false;
		}

		return $result;
	}
}
