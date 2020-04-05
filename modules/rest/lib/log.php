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

	public static function log(\CRestServer $server, $data)
	{
		if(static::checkEntry($server))
		{
			static::addEntry($server, $data);
		}
	}

	public static function checkEntry(\CRestServer $server)
	{
		global $USER;

		$logOptions = Main\Config\Option::get('rest', 'log', 'N');
		if(strlen($logOptions) > 0)
		{
			if($logOptions == 'Y')
			{
				return true;
			}

			$logOptions = unserialize($logOptions);
			if(is_array($logOptions))
			{
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
		}

		return false;
	}

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
}