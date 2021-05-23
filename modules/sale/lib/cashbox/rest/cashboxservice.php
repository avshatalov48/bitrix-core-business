<?php

namespace Bitrix\Sale\Cashbox\Rest;

use Bitrix\Main;
use Bitrix\Rest\AccessException;
use Bitrix\Rest\RestException;
use Bitrix\Sale\Cashbox\Cashbox;
use Bitrix\Sale\Cashbox\CashboxRest;
use Bitrix\Sale\Cashbox\Manager;
use Bitrix\Sale\Cashbox\Ofd;
use Bitrix\Sale\Helpers;
use Bitrix\Sale\Internals\CashboxRestHandlerTable;

if (!Main\Loader::includeModule('rest'))
{
	return;
}

/**
 * Class CashboxService
 * @package Bitrix\Sale\Cashbox\Rest
 */
class CashboxService extends RestService
{
	private const ERROR_CASHBOX_ADD = 'ERROR_CASHBOX_ADD';
	private const ERROR_CASHBOX_NOT_FOUND = 'ERROR_CASHBOX_NOT_FOUND';
	private const ERROR_CASHBOX_UPDATE = 'ERROR_CASHBOX_UPDATE';
	private const ERROR_CASHBOX_DELETE = 'ERROR_CASHBOX_DELETE';

	/**
	 * @param $params
	 * @throws RestException
	 */
	private static function checkParamsBeforeAddCashbox($params)
	{
		if (empty($params['NAME']))
		{
			throw new RestException('Parameter NAME is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (empty($params['REST_CODE']))
		{
			throw new RestException('Parameter REST_CODE is not defined', self::ERROR_CHECK_FAILURE);
		}

		$restHandler = CashboxRestHandlerTable::getList(['filter' => ['=CODE' => $params['REST_CODE']]])->fetch();
		if (!$restHandler)
		{
			throw new RestException("Rest handler with code {$params['REST_CODE']} not found", self::ERROR_CHECK_FAILURE);
		}

		if ($params['APP_ID'] && !empty($restHandler['APP_ID']) && $restHandler['APP_ID'] !== $params['APP_ID'])
		{
			throw new RestException('Access denied', self::ERROR_CHECK_FAILURE);
		}

		if (empty($params['EMAIL']))
		{
			throw new RestException('Parameter EMAIL is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (!empty($params['OFD']))
		{
			$ofdHandlerClass = self::getOfdHandlerClassByCode($params['OFD']);
			if (is_null($ofdHandlerClass))
			{
				throw new RestException('Ofd handler not found', self::ERROR_CHECK_FAILURE);
			}
		}
	}

	/**
	 * @param $params
	 * @throws RestException
	 */
	private static function checkParamsBeforeUpdateCashbox($params)
	{
		if (empty($params['ID']))
		{
			throw new RestException('Parameter ID is not defined', self::ERROR_CHECK_FAILURE);
		}

		$cashbox = Manager::getObjectById($params['ID']);
		if (!$cashbox)
		{
			throw new RestException('Cashbox not found', self::ERROR_CASHBOX_NOT_FOUND);
		}

		$restHandlerCode = $cashbox->getValueFromSettings('REST', 'REST_CODE');
		$isRestCashbox = isset($restHandlerCode);
		if (!$isRestCashbox)
		{
			throw new RestException('Access denied', self::ERROR_CHECK_FAILURE);
		}

		$restHandler = CashboxRestHandlerTable::getList([
			'filter' => [
				'CODE' => $restHandlerCode,
			]
		])->fetch();
		if ($params['APP_ID'] && !empty($restHandler['APP_ID']) && $restHandler['APP_ID'] !== $params['APP_ID'])
		{
			throw new RestException('Access denied', self::ERROR_CHECK_FAILURE);
		}

		if (empty($params['FIELDS']) || !is_array($params['FIELDS']))
		{
			throw new RestException('Parameter FIELDS is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (isset($params['FIELDS']['EMAIL']) && empty($params['FIELDS']['EMAIL']))
		{
			throw new RestException('Parameter EMAIL cannot be empty', self::ERROR_CHECK_FAILURE);
		}

		if (!empty($params['FIELDS']['OFD']))
		{
			$ofdHandlerClass = self::getOfdHandlerClassByCode($params['FIELDS']['OFD']);
			if (is_null($ofdHandlerClass))
			{
				throw new RestException('Ofd handler not found', self::ERROR_CHECK_FAILURE);
			}
		}
	}

	/**
	 * @param $ofdCode
	 * @return string|null
	 */
	private static function getOfdHandlerClassByCode($ofdCode): ?string
	{
		$ofdHandlers = Ofd::getHandlerList();
		foreach ($ofdHandlers as $handler => $name)
		{
			$currentHandlerCode = $handler::getCode();
			if ($currentHandlerCode === $ofdCode)
			{
				return $handler;
			}
		}

		return null;
	}

	/**
	 * @param $params
	 * @throws RestException
	 */
	private static function checkParamsBeforeDeleteCashbox($params)
	{
		if (empty($params['ID']))
		{
			throw new RestException('Parameter ID is not defined', self::ERROR_CHECK_FAILURE);
		}

		$cashbox = Manager::getObjectById($params['ID']);
		if (!$cashbox)
		{
			throw new RestException('Cashbox not found', self::ERROR_CASHBOX_NOT_FOUND);
		}

		$restHandlerCode = $cashbox->getValueFromSettings('REST', 'REST_CODE');
		$isRestCashbox = isset($restHandlerCode);
		if (!$isRestCashbox)
		{
			throw new RestException('Access denied', self::ERROR_CHECK_FAILURE);
		}

		$restHandler = CashboxRestHandlerTable::getList([
			'filter' => [
				'CODE' => $restHandlerCode,
			]
		])->fetch();
		if ($params['APP_ID'] && !empty($restHandler['APP_ID']) && $restHandler['APP_ID'] !== $params['APP_ID'])
		{
			throw new RestException('Access denied', self::ERROR_CHECK_FAILURE);
		}
	}

	/**
	 * @param $params
	 * @return array|int
	 * @throws RestException
	 */
	public static function addCashbox($params, $page, $server)
	{
		Helpers\Rest\AccessChecker::checkAccessPermission();
		$params = self::prepareHandlerParams($params, $server);
		self::checkParamsBeforeAddCashbox($params);

		$settings = $params['SETTINGS'] ?: [];
		$settings['REST']['REST_CODE'] = $params['REST_CODE'];

		$cashboxFields = [
			'NAME' => $params['NAME'],
			'HANDLER' => '\\' . CashboxRest::class,
			'OFD' => empty($params['OFD']) ? '' : self::getOfdHandlerClassByCode($params['OFD']),
			'OFD_SETTINGS' => $params['OFD_SETTINGS'] ?: [],
			'EMAIL' => $params['EMAIL'],
			'NUMBER_KKM' => empty($params['NUMBER_KKM']) ? '' : $params['NUMBER_KKM'],
			'KKM_ID' => empty($params['KKM_ID']) ? '' : $params['KKM_ID'],
			'ACTIVE' => ($params['ACTIVE'] == 'Y') ? 'Y' : 'N',
			'SORT' => is_numeric($params['SORT']) ? (int)$params['SORT'] : 100,
			'USE_OFFLINE' => ($params['USE_OFFLINE'] == 'Y') ? 'Y' : 'N',
			'ENABLED' => 'Y',
			'SETTINGS' => $settings,
		];

		$result = Manager::add($cashboxFields);
		if ($result->isSuccess())
		{
			return $result->getId();
		}

		$errors = implode("\n", $result->getErrorMessages());
		throw new RestException($errors, self::ERROR_CASHBOX_ADD);
	}

	/**
	 * @param $params
	 * @return bool
	 * @throws RestException
	 */
	public static function updateCashbox($params, $page, \CRestServer $server)
	{
		Helpers\Rest\AccessChecker::checkAccessPermission();
		$params = self::prepareHandlerParams($params, $server);
		self::checkParamsBeforeUpdateCashbox($params);

		$allowedFields = [
			'NAME', 'OFD', 'OFD_SETTINGS', 'EMAIL',
			'NUMBER_KKM', 'KKM_ID', 'ACTIVE', 'SORT',
			'USE_OFFLINE', 'ENABLED', 'SETTINGS',
		];

		// remove non-whitelisted keys using $allowedFields values as keys
		$cashboxFields = array_intersect_key($params['FIELDS'], array_flip($allowedFields));

		if (isset($cashboxFields['OFD']))
		{
			$cashboxFields['OFD'] = self::getOfdHandlerClassByCode($cashboxFields['OFD']);
		}

		$result = Manager::update($params['ID'], $cashboxFields);
		if ($result->isSuccess())
		{
			return true;
		}

		$errors = implode("\n", $result->getErrorMessages());
		throw new RestException($errors, self::ERROR_CASHBOX_UPDATE);
	}

	/**
	 * @param $params
	 * @return bool
	 * @throws RestException
	 */
	public static function deleteCashbox($params, $page, $server)
	{
		Helpers\Rest\AccessChecker::checkAccessPermission();
		$params = self::prepareHandlerParams($params, $server);
		self::checkParamsBeforeDeleteCashbox($params);

		$result = Manager::delete($params['ID']);
		if ($result->isSuccess())
		{
			return true;
		}

		$errors = implode("\n", $result->getErrorMessages());
		throw new RestException($errors, self::ERROR_CASHBOX_DELETE);
	}

	/**
	 * @param $params
	 * @return array
	 */
	public static function getCashboxList($params, $page, \CRestServer $server)
	{
		Helpers\Rest\AccessChecker::checkAccessPermission();
		$params = self::prepareHandlerParams($params, $server);

		$allowedFields = [
			'ID', 'NAME', 'OFD', 'OFD_SETTINGS', 'EMAIL',
			'DATE_CREATE', 'DATE_LAST_CHECK', 'NUMBER_KKM', 'KKM_ID',
			'ACTIVE', 'SORT', 'USE_OFFLINE', 'ENABLED', 'SETTINGS',
		];

		$isSelectSpecified = isset($params['SELECT']) && is_array($params['SELECT']);
		if (!$isSelectSpecified || in_array('*', $params['SELECT']))
		{
			$select = $allowedFields;
		}
		else
		{
			$select = array_intersect($allowedFields, $params['SELECT']);
		}

		$isFilterSpecified = isset($params['FILTER']) && is_array($params['FILTER']);
		$filter = [];
		if($isFilterSpecified)
		{
			foreach ($params['FILTER'] as $rawName => $value)
			{
				$filterField = \CSqlUtil::GetFilterOperation($rawName);
				if (isset($filterField['FIELD']) && in_array($filterField['FIELD'], $allowedFields))
				{
					$filter[$rawName] = $value;
				}
			}
		}
		$filter['=HANDLER'] = '\\' . CashboxRest::class;

		$appId = $params['APP_ID'];
		$allowedHandlers = [];
		if ($appId)
		{
			$handlers = Manager::getRestHandlersList();
			$filterByAppID = static function ($handler) use ($appId) {
				return $handler['APP_ID'] === $appId;
			};
			$allowedHandlers = array_keys(array_filter($handlers, $filterByAppID));
		}

		$isOrderSpecified = isset($params['ORDER']) && is_array($params['ORDER']);
		$order = [];
		if ($isOrderSpecified)
		{
			$order = array_intersect_key($params['ORDER'], array_flip($allowedFields));
		}

		$result = array();
		$cashboxListResult = Manager::getList([
			'select' => $select,
			'filter' => $filter,
			'order' => $order,
		]);
		while ($cashbox = $cashboxListResult->fetch())
		{
			$restHandler = $cashbox['SETTINGS']['REST']['REST_CODE'];
			if ($appId && in_array($restHandler, $allowedHandlers))
			{
				$result[] = $cashbox;
			}
		}

		return $result;
	}
}