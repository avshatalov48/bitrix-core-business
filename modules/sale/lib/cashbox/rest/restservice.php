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
 * Class RestService
 * @package Bitrix\Sale\Cashbox
 */
class RestService extends \IRestService
{
	const SCOPE = 'cashbox';

	protected const ERROR_CHECK_FAILURE = 'ERROR_CHECK_FAILURE';

	public static function onRestAppDelete(array $fields): void
	{
		if (!Main\Loader::includeModule('rest'))
		{
			return;
		}

		if (empty($fields['APP_ID']) || empty($fields['CLEAN']) || $fields['CLEAN'] !== true)
		{
			return;
		}

		$app = Rest\AppTable::getByClientId($fields['APP_ID']);
		if (!$app)
		{
			return;
		}

		$restHandlerResult = Sale\Internals\CashboxRestHandlerTable::getList([
			'select' => ['ID', 'CODE'],
			'filter' => [
				'=APP_ID' => $app['CLIENT_ID'],
			],
		]);
		while ($restHandler = $restHandlerResult->fetch())
		{
			$cashboxResult = Sale\Cashbox\Manager::getList([
				'select' => ['ID'],
				'filter' => [
					'=HANDLER' => '\\' . Sale\Cashbox\CashboxRest::class,
				],
			]);
			while ($cashbox = $cashboxResult->fetch())
			{
				$cashboxObj = Sale\Cashbox\Manager::getObjectById($cashbox['ID']);
				if ($cashboxObj)
				{
					$handlerCode = $cashboxObj->getValueFromSettings('REST', 'REST_CODE');
					if ($handlerCode === $restHandler['CODE'])
					{
						Sale\Cashbox\Manager::delete($cashbox['ID']);
					}
				}
			}

			Sale\Internals\CashboxRestHandlerTable::delete($restHandler['ID']);
		}
	}

	/**
	 * @return array
	 */
	public static function onRestServiceBuildDescription()
	{
		return [
			static::SCOPE => [
				// handlers
				'sale.cashbox.handler.add' => [HandlerService::class, 'addHandler'],
				'sale.cashbox.handler.update' => [HandlerService::class, 'updateHandler'],
				'sale.cashbox.handler.delete' => [HandlerService::class, 'deleteHandler'],
				'sale.cashbox.handler.list' => [HandlerService::class, 'getHandlerList'],

				// cashbox
				'sale.cashbox.add' => [CashboxService::class, 'addCashbox'],
				'sale.cashbox.update' => [CashboxService::class, 'updateCashbox'],
				'sale.cashbox.delete' => [CashboxService::class, 'deleteCashbox'],
				'sale.cashbox.list' => [CashboxService::class, 'getCashboxList'],

				'sale.cashbox.settings.get' => [CashboxService::class, 'getCashboxSettings'],
				'sale.cashbox.settings.update' => [CashboxService::class, 'updateCashboxSettings'],

				'sale.cashbox.ofd.settings.get' => [CashboxService::class, 'getCashboxOfdSettings'],
				'sale.cashbox.ofd.settings.update' => [CashboxService::class, 'updateCashboxOfdSettings'],

				// check
				'sale.cashbox.check.apply' => [CheckService::class, 'applyCheck'],

				// ofd
				'sale.ofd.list' => [OfdService::class, 'getOfdList'],
				'sale.ofd.settings.get' => [OfdService::class, 'getOfdSettings'],
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