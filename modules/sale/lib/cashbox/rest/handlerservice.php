<?php

namespace Bitrix\Sale\Cashbox\Rest;

use Bitrix\Main;
use Bitrix\Rest\AccessException;
use Bitrix\Rest\RestException;
use Bitrix\Sale\Cashbox\CashboxRest;
use Bitrix\Sale\Cashbox\Manager;
use Bitrix\Sale\Internals\CashboxRestHandlerTable;
use Bitrix\Sale\Helpers;
use Bitrix\Sale\Location\Exception;

if (!Main\Loader::includeModule('rest'))
{
	return;
}

/**
 * Class HandlerService
 * @package Bitrix\Sale\Cashbox\Rest
 */
class HandlerService extends RestService
{
	private const ERROR_HANDLER_ALREADY_EXISTS = 'ERROR_HANDLER_ALREADY_EXIST';
	private const ERROR_HANDLER_NOT_FOUND = 'ERROR_HANDLER_NOT_FOUND';
	private const ERROR_HANDLER_UPDATE = 'ERROR_HANDLER_UPDATE';
	private const ERROR_HANDLER_DELETE = 'ERROR_HANDLER_DELETE';

	/**
	 * @param $params
	 * @throws RestException
	 */
	private static function checkParamsBeforeAddHandler($params)
	{
		if (empty($params['CODE']))
		{
			throw new RestException('Parameter CODE is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (empty($params['NAME']))
		{
			throw new RestException('Parameter NAME is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (empty($params['SETTINGS']) || !is_array($params['SETTINGS']))
		{
			throw new RestException('Parameter SETTINGS is not defined or empty', self::ERROR_CHECK_FAILURE);
		}

		self::checkHandlerSettings($params['SETTINGS']);

		$handler = CashboxRestHandlerTable::getList([
			'filter' => [
				'=CODE' => $params['CODE']
			]
		])->fetch();
		if ($handler)
		{
			throw new RestException('Handler already exists!', self::ERROR_HANDLER_ALREADY_EXISTS);
		}
	}

	/**
	 * @param $params
	 * @throws RestException
	 */
	private static function checkParamsBeforeUpdateHandler($params)
	{
		if (empty($params['ID']))
		{
			throw new RestException('Parameter ID is not defined', self::ERROR_CHECK_FAILURE);
		}

		$handler = CashboxRestHandlerTable::getList([
			'filter' => [
				'ID' => $params['ID']
			]
		])->fetch();
		if (!$handler)
		{
			throw new RestException('Handler not found', self::ERROR_HANDLER_NOT_FOUND);
		}

		if ($params['APP_ID'] && !empty($handler['APP_ID']) && $handler['APP_ID'] !== $params['APP_ID'])
		{
			throw new RestException('Access denied', self::ERROR_CHECK_FAILURE);
		}

		if (empty($params['FIELDS']) || !is_array($params['FIELDS']))
		{
			throw new RestException('Parameter FIELDS is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (isset($params['FIELDS']['SETTINGS']))
		{
			self::checkHandlerSettings($params['FIELDS']['SETTINGS']);
		}
	}

	/**
	 * @param $params
	 * @throws RestException
	 */
	private static function checkParamsBeforeDeleteHandler($params)
	{
		if (empty($params['ID']))
		{
			throw new RestException('Parameter ID is not defined', self::ERROR_CHECK_FAILURE);
		}

		$handler = CashboxRestHandlerTable::getList([
			'filter' => [
				'ID' => $params['ID']
			]
		])->fetch();
		if (!$handler)
		{
			throw new RestException('Handler not found', self::ERROR_HANDLER_NOT_FOUND);
		}

		if ($params['APP_ID'] && !empty($handler['APP_ID']) && $handler['APP_ID'] !== $params['APP_ID'])
		{
			throw new RestException('Access denied', self::ERROR_CHECK_FAILURE);
		}

		$cashboxListResult = Manager::getList([
			'select' => ['ID', 'HANDLER', 'SETTINGS'],
			'filter' => [
				'=HANDLER' => '\\'.CashboxRest::class,
			],
		]);

		$cashboxIdList = [];
		while ($cashbox = $cashboxListResult->fetch())
		{
			if ($cashbox['SETTINGS']['REST']['REST_CODE'] === $handler['CODE'])
			{
				$cashboxIdList[] = $cashbox['ID'];
			}
		}

		if ($cashboxIdList)
		{
			throw new RestException(
				'There are cashboxes with this handler: '.implode(', ', $cashboxIdList),
				self::ERROR_CHECK_FAILURE
			);
		}
	}

	/**
	 * @param $settings
	 * @throws RestException
	 */
	private static function checkHandlerSettings($settings)
	{
		if (empty($settings['PRINT_URL']))
		{
			throw new RestException('Parameter SETTINGS[PRINT_URL] is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (empty($settings['CHECK_URL']))
		{
			throw new RestException('Parameter SETTINGS[CHECK_URL] is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (empty($settings['CONFIG']))
		{
			throw new RestException('Parameter SETTINGS[CONFIG] is not defined', self::ERROR_CHECK_FAILURE);
		}
	}

	/**
	 * @param $params
	 * @param \CRestServer $server
	 * @return array|bool|int
	 */
	public static function addHandler($params, $page, \CRestServer $server)
	{
		Helpers\Rest\AccessChecker::checkAccessPermission();
		$params = self::prepareHandlerParams($params, $server);
		self::checkParamsBeforeAddHandler($params);

		if (!isset($params['SETTINGS']['SUPPORTS_FFD105']))
		{
			$params['SETTINGS']['SUPPORTS_FFD105'] = 'N';
		}

		$result = CashboxRestHandlerTable::add([
			'NAME' => $params['NAME'],
			'CODE' => $params['CODE'],
			'SORT' => $params['SORT'] ?: 100,
			'SETTINGS' => $params['SETTINGS'],
			'APP_ID' => $params['APP_ID'],
		]);

		if ($result->isSuccess())
		{
			return $result->getId();
		}

		return false;
	}

	/**
	 * @param $params
	 * @return bool
	 * @throws RestException
	 */
	public static function updateHandler($params, $page, \CRestServer $server)
	{
		Helpers\Rest\AccessChecker::checkAccessPermission();
		$params = self::prepareHandlerParams($params, $server);
		self::checkParamsBeforeUpdateHandler($params);

		$result = CashboxRestHandlerTable::update($params['ID'], $params['FIELDS']);
		if ($result->isSuccess())
		{
			return true;
		}

		$errors = implode("\n", $result->getErrorMessages());
		throw new RestException($errors, self::ERROR_HANDLER_UPDATE);
	}

	/**
	 * @param $params
	 * @return bool
	 * @throws RestException
	 */
	public static function deleteHandler($params, $page, \CRestServer $server)
	{
		Helpers\Rest\AccessChecker::checkAccessPermission();
		$params = self::prepareHandlerParams($params, $server);
		self::checkParamsBeforeDeleteHandler($params);

		$result = CashboxRestHandlerTable::delete($params['ID']);
		if ($result->isSuccess())
		{
			return true;
		}

		$errors = implode("\n", $result->getErrorMessages());
		throw new RestException($errors, self::ERROR_HANDLER_DELETE);
	}

	/**
	 * @return array
	 */
	public static function getHandlerList($params, $page, \CRestServer $server)
	{
		Helpers\Rest\AccessChecker::checkAccessPermission();
		$params = self::prepareHandlerParams($params, $server);
		$appId = $params['APP_ID'];

		$handlers = Manager::getRestHandlersList();
		if ($appId)
		{
			$filterByAppID = static function ($handler) use ($appId) {
				return $handler['APP_ID'] === $appId;
			};
			$handlers = array_filter($handlers, $filterByAppID);
		}

		return $handlers;
	}
}