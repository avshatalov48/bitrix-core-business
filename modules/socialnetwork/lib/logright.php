<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main\Entity;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Application;

class LogRightTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sonet_log_right';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'LOG_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'LOG' => array(
				'data_type' => '\Bitrix\Socialnetwork\Log',
				'reference' => array('=this.LOG_ID' => 'ref.ID')
			),
			'GROUP_CODE' => array(
				'data_type' => 'string',
			)
		);

		return $fieldsMap;
	}

	public static function setLogUpdate($params = array())
	{
		$logId = (isset($params['logId']) ? intval($params['logId']) : 0);
		$value = (!empty($params['value']) ? $params['value'] : false);

		if ($logId <= 0)
		{
			return false;
		}

		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();

		$now = $helper->getCurrentDateTimeFunction();
		if (
			!$value
			|| strtolower($value) == strtolower($now)
		)
		{
			$value = new SqlExpression($now);
		}

		$updateFields = array(
			"LOG_UPDATE" => $value,
		);

		$tableName = self::getTableName();
		list($prefix, $values) = $helper->prepareUpdate($tableName, $updateFields);
		$connection->queryExecute("UPDATE {$tableName} SET {$prefix} WHERE `LOG_ID` = ".$logId);

		return true;
	}
}
