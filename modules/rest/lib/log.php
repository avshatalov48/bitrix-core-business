<?php
namespace Bitrix\Rest;

use Bitrix\Main;

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
 **/
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
		);
	}

	/**
	 * Checks if logging is applicable to the data and logs it if this is the case.
	 *
	 * @param \CRestServer $server REST call context.
	 * @param string $data Response content.
	 *
	 * @return void
	 *
	 * @see \Bitrix\Rest\Log::checkEntry
	 * @see \Bitrix\Rest\Log::addEntry
	 */
	public static function log(\CRestServer $server, $data)
	{
		if(static::checkEntry($server))
		{
			static::addEntry($server, $data);
		}
	}

	/**
	 * Checks if logging is applicable to the rest call.
	 *
	 * @param \CRestServer $server REST call context.
	 *
	 * @return void
	 */
	public static function checkEntry(\CRestServer $server)
	{
		global $USER;

		$logEndTime = intval(\Bitrix\Main\Config\Option::get('rest', 'log_end_time', 0));
		if ($logEndTime < time())
		{
			return false;
		}

		$logOptions = @unserialize(\Bitrix\Main\Config\Option::get('rest', 'log_filters', ''));
		if (!is_array($logOptions))
		{
			$logOptions = array();
		}

		if(
			isset($logOptions['client_id']) && $server->getClientId() !== $logOptions['client_id']
			|| isset($logOptions['password_id']) && $server->getPasswordId() !== $logOptions['password_id']
			|| isset($logOptions['scope']) && $server->getScope() !== $logOptions['scope']
			|| isset($logOptions['method']) && $server->getMethod() !== $logOptions['method']
			|| isset($logOptions['user_id']) && $USER->getId() !== $logOptions['user_id']
		)
		{
			return false;
		}

		return true;
	}

	/**
	 * Adds a log entry.
	 *
	 * @param \CRestServer $server REST call context.
	 * @param string $data Response content.
	 *
	 * @return void
	 */
	public static function addEntry(\CRestServer $server, $data)
	{
		$request = Main\Context::getCurrent()->getRequest();

		static::add(array(
			'CLIENT_ID' => $server->getClientId(),
			'PASSWORD_ID' => $server->getPasswordId(),
			'SCOPE' => $server->getScope(),
			'METHOD' => $server->getMethod(),
			'REQUEST_METHOD' => $request->getRequestMethod(),
			'REQUEST_URI' => $request->getRequestUri(),
			'REQUEST_AUTH' => $server->getAuth(),
			'REQUEST_DATA' => $server->getQuery(),
			'RESPONSE_STATUS' => \CHTTP::getLastStatus(),
			'RESPONSE_DATA' => $data,
		));
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
}