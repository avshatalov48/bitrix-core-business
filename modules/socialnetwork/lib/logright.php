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

/**
 * Class LogRightTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_LogRight_Query query()
 * @method static EO_LogRight_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_LogRight_Result getById($id)
 * @method static EO_LogRight_Result getList(array $parameters = [])
 * @method static EO_LogRight_Entity getEntity()
 * @method static \Bitrix\Socialnetwork\EO_LogRight createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\EO_LogRight_Collection createCollection()
 * @method static \Bitrix\Socialnetwork\EO_LogRight wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\EO_LogRight_Collection wakeUpCollection($rows)
 */
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
			),
			'LOG_UPDATE' => array(
				'data_type' => 'datetime'
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
			|| mb_strtolower($value) == mb_strtolower($now)
		)
		{
			$value = new SqlExpression($now);
		}

		$updateFields = array(
			"LOG_UPDATE" => $value,
		);

		$tableName = self::getTableName();
		list($prefix, $values) = $helper->prepareUpdate($tableName, $updateFields);
		$connection->queryExecute("UPDATE {$tableName} SET {$prefix} WHERE LOG_ID = ".$logId);

		return true;
	}

	public static function deleteByGroupCode($value = '')
	{
		if ($value == '')
		{
			return false;
		}

		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();

		$tableName = self::getTableName();
		$connection->queryExecute("DELETE FROM {$tableName} WHERE GROUP_CODE = '".$helper->forSql($value)."'");

		return true;
	}
}
