<?php
namespace Bitrix\Rest;

use Bitrix\Main;
use Bitrix\Main\Request;

/**
 * Class LogTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TIMESTAMP_X datetime mandatory default 'CURRENT_TIMESTAMP'
 * <li> CLIENT_ID string(45) optional
 * <li> PASSWORD_ID int optional
 * <li> SCOPE string(50) optional
 * <li> METHOD string(255) optional
 * <li> REQUEST_URI string(255) optional
 * <li> REQUEST_DATA string optional
 * <li> RESPONSE_DATA string optional
 * </ul>
 *
 * @package Bitrix\Rest
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Log_Query query()
 * @method static EO_Log_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Log_Result getById($id)
 * @method static EO_Log_Result getList(array $parameters = array())
 * @method static EO_Log_Entity getEntity()
 * @method static \Bitrix\Rest\EO_Log createObject($setDefaultValues = true)
 * @method static \Bitrix\Rest\EO_Log_Collection createCollection()
 * @method static \Bitrix\Rest\EO_Log wakeUpObject($row)
 * @method static \Bitrix\Rest\EO_Log_Collection wakeUpCollection($rows)
 */
class LogTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_rest_log';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'TIMESTAMP_X' => array(
				'data_type' => 'datetime',
			),
			'CLIENT_ID' => array(
				'data_type' => 'string',
			),
			'PASSWORD_ID' => array(
				'data_type' => 'integer',
			),
			'SCOPE' => array(
				'data_type' => 'string',
			),
			'METHOD' => array(
				'data_type' => 'string',
			),
			'REQUEST_METHOD' => array(
				'data_type' => 'string',
			),
			'REQUEST_URI' => array(
				'data_type' => 'string',
			),
			'REQUEST_AUTH' => array(
				'data_type' => 'text',
				'serialized' => true,
			),
			'REQUEST_DATA' => array(
				'data_type' => 'text',
				'serialized' => true,
			),
			'RESPONSE_STATUS' => array(
				'data_type' => 'string',
			),
			'RESPONSE_DATA' => array(
				'data_type' => 'text',
				'serialized' => true,
			),
			'EVENT_ID' => array(
				'data_type' => 'integer',
			),
			'MESSAGE' => array(
				'data_type' => 'text',
			),
		);
	}

	public static function getCountAll()
	{
		$entity = static::getEntity();
		$sqlTableName = static::getTableName();

		$sql = "SELECT count(1) CNT FROM {$sqlTableName}";
		$query = $entity->getConnection()->query($sql);
		return $query->fetch()["CNT"];
	}

	public static function clearAll()
	{
		$entity = static::getEntity();
		$sqlTableName = static::getTableName();

		$sql = "TRUNCATE TABLE {$sqlTableName}";
		$entity->getConnection()->queryExecute($sql);
	}

	public static function cleanUpAgent()
	{
		$entity = static::getEntity();
		$sqlTableName = static::getTableName();
		$connection = $entity->getConnection();

		$lastIdQuery = $connection->query("
			SELECT max(ID) MID
			from {$sqlTableName}
		");
		$lastId = $lastIdQuery->fetch();
		if ($lastId && $lastId['MID'])
		{
			$date = new Main\Type\DateTime();
			$date->add("-7D");

			$lastTimeQuery = $connection->query("
				SELECT TIMESTAMP_X
				from {$sqlTableName}
				WHERE ID = $lastId[MID]
			");
			$lastTime = $lastTimeQuery->fetch();
			if ($lastTime && $lastTime['TIMESTAMP_X'] < $date)
			{
				static::clearAll();
			}
		}

		return "\\Bitrix\\Rest\\LogTable::cleanUpAgent();";
	}

	public static function filterResponseData(&$data): void
	{
		//filter non-searizable objects
		if (is_object($data) && !method_exists($data, '__serialize'))
		{
			$data = '';
		}
		else if (is_array($data))
		{
			foreach ($data as &$oneData)
			{
				static::filterResponseData($oneData);
			}
		}
	}
}