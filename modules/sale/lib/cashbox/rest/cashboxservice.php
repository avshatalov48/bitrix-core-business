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

	private const ALLOWED_CASHBOX_FIELDS = [
		'ID', 'NAME', 'OFD', 'EMAIL',
		'DATE_CREATE', 'DATE_LAST_CHECK', 'NUMBER_KKM', 'KKM_ID',
		'ACTIVE', 'SORT', 'USE_OFFLINE', 'ENABLED',
	];

	/**
	 * @param $params
	 * @throws RestException
	 * @throws AccessException
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
			throw new AccessException();
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
	 * @throws AccessException
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

		if (!self::hasAccessToCashbox($cashbox, $params['APP_ID']))
		{
			throw new AccessException();
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
	 * @throws AccessException
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

		if (!self::hasAccessToCashbox($cashbox, $params['APP_ID']))
		{
			throw new AccessException();
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
			'ACTIVE' => ($params['ACTIVE'] === 'Y') ? 'Y' : 'N',
			'SORT' => is_numeric($params['SORT']) ? (int)$params['SORT'] : 100,
			'USE_OFFLINE' => ($params['USE_OFFLINE'] === 'Y') ? 'Y' : 'N',
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

		if ($cashboxFields['SETTINGS'])
		{
			$cashboxFields['SETTINGS'] = self::mergeCashboxSettings($params['ID'], $cashboxFields['SETTINGS']);
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
	 * @param $cashboxId
	 * @param $newSettings
	 * @return array|null
	 */
	private static function mergeCashboxSettings($cashboxId, $newSettings)
	{
		$existingSettings = Manager::getList([
			'select' => ['SETTINGS'],
			'filter' => ['=ID' => $cashboxId],
			'limit' => 1,
		])->fetch()['SETTINGS'];

		if (!$existingSettings)
		{
			return $newSettings;
		}

		$mergedSettings = array_replace_recursive($existingSettings, $newSettings);
		return $mergedSettings;
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
	 * @param $page
	 * @param \CRestServer $server
	 * @return array
	 */
	public static function getCashboxList($params, $page, \CRestServer $server)
	{
		Helpers\Rest\AccessChecker::checkAccessPermission();
		$params = self::prepareHandlerParams($params, $server);
		self::checkParamsBeforeCashboxListGet($params);

		$select =
			isset($params['SELECT']) && is_array($params['SELECT'])
				? array_flip(self::prepareIncomingParams(array_flip($params['SELECT'])))
				: self::ALLOWED_CASHBOX_FIELDS
		;

		$filter = isset($params['FILTER']) && is_array($params['FILTER']) ? $params['FILTER'] : [];
		$order = isset($params['ORDER']) && is_array($params['ORDER']) ? $params['ORDER'] : [];

		$result = [];
		$cashboxListResult = Manager::getList([
			'select' => $select,
			'filter' => $filter,
			'order' => $order,
		]);
		while ($cashbox = $cashboxListResult->fetch())
		{
			if ($cashbox['OFD'])
			{
				$cashbox['OFD'] = $cashbox['OFD']::getCode();
			}

			$result[] = $cashbox;
		}

		return $result;
	}

	private static function checkParamsBeforeCashboxListGet(array $params)
	{
		$select = isset($params['SELECT']) && is_array($params['SELECT']) ? $params['SELECT'] : [];
		if ($select)
		{
			$select = array_flip(self::prepareIncomingParams(array_flip($select)));
			$diffSelect = array_diff($select, self::ALLOWED_CASHBOX_FIELDS);

			if ($diffSelect)
			{
				throw new RestException(implode(', ', $diffSelect) . ' not allowed for select');
			}
		}

		$filter = isset($params['FILTER']) && is_array($params['FILTER']) ? $params['FILTER'] : [];
		if ($filter)
		{
			$filterFields = [];
			foreach ($filter as $rawName => $value)
			{
				$filterField = \CSqlUtil::GetFilterOperation($rawName);
				if (isset($filterField['FIELD']))
				{
					$filterFields[] = $filterField['FIELD'];
				}
			}

			$filterFields = array_flip(self::prepareIncomingParams(array_flip($filterFields)));
			$diffFilter = array_diff($filterFields, self::ALLOWED_CASHBOX_FIELDS);
			if ($diffFilter)
			{
				throw new RestException(implode(', ', $diffFilter) . ' not allowed for filter');
			}
		}

		$order =
			isset($params['ORDER']) && is_array($params['ORDER'])
				? self::prepareIncomingParams($params['ORDER'])
				: []
		;
		if ($order)
		{
			$diffOrder = array_diff(array_keys($order), self::ALLOWED_CASHBOX_FIELDS);
			if ($diffOrder)
			{
				throw new RestException(implode(', ', $diffOrder) . ' not allowed for order');
			}
		}
	}

	/**
	 * @param $params
	 * @param $page
	 * @param \CRestServer $server
	 * @return array|mixed
	 */
	public static function getCashboxSettings($params, $page, \CRestServer $server)
	{
		Helpers\Rest\AccessChecker::checkAccessPermission();
		$params = self::prepareHandlerParams($params, $server);
		self::checkParamsBeforeCashboxSettingsGet($params);

		$cashbox = Manager::getObjectById($params['ID']);
		if ($cashbox)
		{
			$settings = $cashbox->getField('SETTINGS');
			unset($settings['REST']);

			return $settings;
		}

		return [];
	}

	/**
	 * @param $params
	 * @param $page
	 * @param \CRestServer $server
	 * @return array|mixed
	 */
	public static function getCashboxOfdSettings($params, $page, \CRestServer $server)
	{
		Helpers\Rest\AccessChecker::checkAccessPermission();
		$params = self::prepareHandlerParams($params, $server);
		self::checkParamsBeforeCashboxSettingsGet($params);

		$cashbox = Manager::getObjectById($params['ID']);
		if ($cashbox)
		{
			return $cashbox->getField('OFD_SETTINGS');
		}

		return [];
	}

	/**
	 * @param array $params
	 * @throws AccessException
	 * @throws RestException
	 */
	private static function checkParamsBeforeCashboxSettingsGet(array $params)
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

		if (!self::hasAccessToCashbox($cashbox, $params['APP_ID']))
		{
			throw new AccessException();
		}
	}

	/**
	 * @param $params
	 * @param $page
	 * @param \CRestServer $server
	 * @return bool
	 * @throws RestException
	 */
	public static function updateCashboxSettings($params, $page, \CRestServer $server)
	{
		Helpers\Rest\AccessChecker::checkAccessPermission();
		$params = self::prepareHandlerParams($params, $server);
		self::checkParamsBeforeCashboxSettingsUpdate($params);

		$cashbox = Manager::getObjectById($params['ID']);
		$restHandlerCode = $cashbox->getValueFromSettings('REST', 'REST_CODE');

		$params['FIELDS']['REST']['REST_CODE'] = $restHandlerCode;

		$result = Manager::update($params['ID'], ['SETTINGS' => $params['FIELDS']]);
		if ($result->isSuccess())
		{
			return true;
		}

		$errors = implode("\n", $result->getErrorMessages());
		throw new RestException($errors, self::ERROR_CASHBOX_UPDATE);
	}

	public static function updateCashboxOfdSettings($params, $page, \CRestServer $server)
	{
		Helpers\Rest\AccessChecker::checkAccessPermission();
		$params = self::prepareHandlerParams($params, $server);
		self::checkParamsBeforeCashboxSettingsUpdate($params);

		$result = Manager::update($params['ID'], ['OFD_SETTINGS' => $params['FIELDS']]);
		if ($result->isSuccess())
		{
			return true;
		}

		$errors = implode("\n", $result->getErrorMessages());
		throw new RestException($errors, self::ERROR_CASHBOX_UPDATE);
	}

	/**
	 * @param array $params
	 * @throws AccessException
	 * @throws RestException
	 */
	private static function checkParamsBeforeCashboxSettingsUpdate(array $params)
	{
		if (empty($params['ID']))
		{
			throw new RestException('Parameter ID is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (empty($params['FIELDS']))
		{
			throw new RestException('Parameter FIELDS is not defined', self::ERROR_CHECK_FAILURE);
		}

		$cashbox = Manager::getObjectById($params['ID']);
		if (!$cashbox)
		{
			throw new RestException('Cashbox not found', self::ERROR_CASHBOX_NOT_FOUND);
		}

		if (!self::hasAccessToCashbox($cashbox, $params['APP_ID']))
		{
			throw new AccessException();
		}
	}

	private static function hasAccessToCashbox(Cashbox $cashbox, string $appId = null): bool
	{
		$handler = $cashbox->getField('HANDLER');
		if (self::isRestHandler($handler))
		{
			$restHandlerCode = $cashbox->getValueFromSettings('REST', 'REST_CODE');

			$handlerData = self::getHandlerData($restHandlerCode);
			if ($appId && !empty($handlerData['APP_ID']) && $handlerData['APP_ID'] !== $appId)
			{
				return false;
			}
		}
		else
		{
			return false;
		}

		return true;
	}

	private static function isRestHandler(string $handler): bool
	{
		return $handler === '\\' . CashboxRest::class;
	}

	private static function getHandlerData(string $code): ?array
	{
		static $result = [];

		if (!empty($result[$code]))
		{
			return $result[$code];
		}

		$handlerData = CashboxRestHandlerTable::getList([
			'filter' => ['CODE' => $code],
			'limit' => 1,
		])->fetch();
		if ($handlerData)
		{
			$result[$code] = $handlerData;
		}

		return $result[$code] ?? null;
	}
}