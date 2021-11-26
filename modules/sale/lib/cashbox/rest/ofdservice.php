<?php

namespace Bitrix\Sale\Cashbox\Rest;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Rest;

if (!Main\Loader::includeModule('rest'))
{
	return;
}

/**
 *
 */
class OfdService extends RestService
{
	/**
	 * @param $params
	 * @param $page
	 * @param $server
	 * @return array
	 */
	public static function getOfdList($params, $page, $server)
	{
		Sale\Helpers\Rest\AccessChecker::checkAccessPermission();

		return array_keys(self::getOfdHandlersMap());
	}

	/**
	 * @param $params
	 * @param $page
	 * @param $server
	 * @return array|array[]
	 */
	public static function getOfdSettings($params, $page, $server)
	{
		Sale\Helpers\Rest\AccessChecker::checkAccessPermission();
		$params = self::prepareHandlerParams($params, $server);
		self::checkParamsBeforeOfdSettingsGet($params);

		$ofdHandlersMap = self::getOfdHandlersMap();
		/** @var Sale\Cashbox\Ofd $ofdClass */
		$ofdClass = $ofdHandlersMap[$params['OFD']];
		return $ofdClass::getSettings();
	}

	/**
	 * @param array $params
	 * @throws Rest\RestException
	 */
	private static function checkParamsBeforeOfdSettingsGet(array $params)
	{
		if (empty($params['OFD']))
		{
			throw new Rest\RestException('Parameter OFD is not defined', self::ERROR_CHECK_FAILURE);
		}

		$ofdHandlersMap = self::getOfdHandlersMap();
		if (!isset($ofdHandlersMap[$params['OFD']]))
		{
			throw new Rest\RestException('Ofd not found', self::ERROR_CHECK_FAILURE);
		}
	}

	/**
	 * @return array
	 */
	private static function getOfdHandlersMap(): array
	{
		static $result = [];

		if (empty($result))
		{
			$ofdHandlers = array_keys(Sale\Cashbox\Ofd::getHandlerList());
			/** @var Sale\Cashbox\Ofd $handler */
			foreach ($ofdHandlers as $handler)
			{
				$result[$handler::getCode()] = $handler;
			}
		}

		return $result;
	}
}