<?php

namespace Bitrix\Sale\Cashbox\Rest;

use Bitrix\Main;
use Bitrix\Rest\AccessException;
use Bitrix\Rest\RestException;
use Bitrix\Sale\Cashbox\Cashbox;
use Bitrix\Sale\Cashbox\Manager;
use Bitrix\Sale\Cashbox\Ofd;
use Bitrix\Sale\Helpers;

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
	 * @throws Main\NotImplementedException
	 * @throws RestException
	 */
	private static function checkParamsBeforeAddCashbox($params)
	{
		if (empty($params['NAME']))
		{
			throw new RestException('Parameter NAME is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (empty($params['HANDLER']))
		{
			throw new RestException('Parameter HANDLER is not defined', self::ERROR_CHECK_FAILURE);
		}
		$cashboxHandlerList = Cashbox::getHandlerList();
		if (!isset($cashboxHandlerList[$params['HANDLER']]))
		{
			throw new RestException('Cashbox handler not found', self::ERROR_CHECK_FAILURE);
		}

		if (empty($params['EMAIL']))
		{
			throw new RestException('Parameter EMAIL is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (!empty($params['OFD']))
		{
			$ofdHandlerList = Ofd::getHandlerList();
			if (!isset($ofdHandlerList[$params['OFD']]))
			{
				throw new RestException('Ofd handler not found', self::ERROR_CHECK_FAILURE);
			}
		}
	}

	/**
	 * @param $params
	 * @throws Main\NotImplementedException
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

		if (empty($params['FIELDS']) || !is_array($params['FIELDS']))
		{
			throw new RestException('Parameter FIELDS is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (isset($params['FIELDS']['HANDLER']))
		{
			$cashboxHandlerList = Cashbox::getHandlerList();
			if (!isset($cashboxHandlerList[$params['FIELDS']['HANDLER']]))
			{
				throw new RestException('Cashbox handler not found', self::ERROR_CHECK_FAILURE);
			}
		}

		if (isset($params['FIELDS']['EMAIL']) && $params['FIELDS']['EMAIL'] == false)
		{
			throw new RestException('Parameter EMAIL cannot be empty', self::ERROR_CHECK_FAILURE);
		}

		if (!empty($params['FIELDS']['OFD']))
		{
			$ofdHandlerList = Ofd::getHandlerList();
			if (!isset($ofdHandlerList[$params['FIELDS']['OFD']]))
			{
				throw new RestException('Ofd handler not found', self::ERROR_CHECK_FAILURE);
			}
		}
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

		$handler = Manager::getObjectById($params['ID']);
		if (!$handler)
		{
			throw new RestException('Cashbox not found', self::ERROR_CASHBOX_NOT_FOUND);
		}
	}

	/**
	 * @param $params
	 * @return array|int
	 * @throws AccessException
	 * @throws Main\LoaderException
	 * @throws Main\NotImplementedException
	 * @throws RestException
	 */
	public static function addCashbox($params)
	{
		Helpers\Rest\AccessChecker::checkAccessPermission();
		$params = self::prepareParams($params);
		self::checkParamsBeforeAddCashbox($params);

		$cashboxFields = [
			'NAME' => $params['NAME'],
			'HANDLER' => $params['HANDLER'],
			'OFD' => empty($params['OFD']) ? '' : $params['OFD'],
			'OFD_SETTINGS' => $params['OFD_SETTINGS'] ?: [],
			'EMAIL' => $params['EMAIL'],
			'NUMBER_KKM' => empty($params['NUMBER_KKM']) ? '' : $params['NUMBER_KKM'],
			'KKM_ID' => empty($params['KKM_ID']) ? '' : $params['KKM_ID'],
			'ACTIVE' => ($params['ACTIVE'] == 'Y') ? 'Y' : 'N',
			'SORT' => is_numeric($params['SORT']) ? (int)$params['SORT'] : 100,
			'USE_OFFLINE' => ($params['USE_OFFLINE'] == 'Y') ? 'Y' : 'N',
			'ENABLED' => ($params['ENABLED'] == 'Y') ? 'Y' : 'N',
			'SETTINGS' => $params['SETTINGS'] ?: [],
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
	 * @throws AccessException
	 * @throws Main\LoaderException
	 * @throws Main\NotImplementedException
	 * @throws RestException
	 */
	public static function updateCashbox($params)
	{
		Helpers\Rest\AccessChecker::checkAccessPermission();
		$params = self::prepareParams($params);
		self::checkParamsBeforeUpdateCashbox($params);

		$allowedFields = [
			'NAME', 'HANDLER', 'OFD', 'OFD_SETTINGS', 'EMAIL',
			'NUMBER_KKM', 'KKM_ID', 'ACTIVE', 'SORT',
			'USE_OFFLINE', 'ENABLED', 'SETTINGS',
		];

		// remove non-whitelisted keys using $allowedFields values as keys
		$cashboxFields = array_intersect_key($params['FIELDS'], array_flip($allowedFields));

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
	 * @throws AccessException
	 * @throws RestException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function deleteCashbox($params)
	{
		Helpers\Rest\AccessChecker::checkAccessPermission();
		$params = self::prepareParams($params);
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
	 * @throws AccessException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getCashboxList($params)
	{
		Helpers\Rest\AccessChecker::checkAccessPermission();
		$params = self::prepareParams($params);

		$allowedFields = [
			'ID', 'NAME', 'HANDLER', 'OFD', 'OFD_SETTINGS', 'EMAIL',
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
			$result[] = $cashbox;
		}

		return $result;
	}
}