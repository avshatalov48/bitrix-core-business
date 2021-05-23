<?php

namespace Bitrix\Sale\Cashbox\Rest;

use Bitrix\Main;
use Bitrix\Rest\AccessException;

if (!Main\Loader::includeModule('rest'))
{
	return;
}

/**
 * Class RestService
 * @package Bitrix\Sale\Cashbox
 */
class RestService extends \IRestService
{
	const SCOPE = "cashbox";

	protected const ERROR_CHECK_FAILURE = 'ERROR_CHECK_FAILURE';

	/**
	 * @return array
	 */
	public static function onRestServiceBuildDescription()
	{
		return [
			static::SCOPE => [
				'sale.cashbox.handler.add' => [HandlerService::class, 'addHandler'],
				'sale.cashbox.handler.update' => [HandlerService::class, 'updateHandler'],
				'sale.cashbox.handler.delete' => [HandlerService::class, 'deleteHandler'],
				'sale.cashbox.handler.list' => [HandlerService::class, 'getHandlerList'],

				'sale.cashbox.add' => [CashboxService::class, 'addCashbox'],
				'sale.cashbox.update' => [CashboxService::class, 'updateCashbox'],
				'sale.cashbox.delete' => [CashboxService::class, 'deleteCashbox'],
				'sale.cashbox.list' => [CashboxService::class, 'getCashboxList'],

				'sale.cashbox.check.apply' => [CheckService::class, 'applyCheck'],
			]
		];
	}

	/**
	 * @param array $data
	 * @return array
	 */
	protected static function prepareIncomingParams(array $data)
	{
		return self::arrayChangeKeyCaseRecursive($data);
	}

	/**
	 * @param $array
	 * @param int $case
	 * @return array
	 */
	private static function arrayChangeKeyCaseRecursive($array, $case = CASE_UPPER)
	{
		$result = $array;
		foreach ($result as $key => $value)
		{
			if (is_array($result[$key]))
			{
				$result[$key] = self::arrayChangeKeyCaseRecursive($result[$key], $case);
			}
		}

		$result = array_change_key_case($result, $case);

		return $result;
	}

	/**
	 * @param $data
	 * @param $server
	 * @return array
	 */
	protected static function prepareHandlerParams($data, \CRestServer $server)
	{
		$data = self::prepareIncomingParams($data);
		$data['APP_ID'] = $server->getClientId();

		return $data;
	}
}