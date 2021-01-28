<?php

namespace Bitrix\Sale\Delivery\Rest;

use Bitrix\Main,
	Bitrix\Sale,
	Bitrix\Rest\AccessException,
	Bitrix\Rest\RestException;

if (!Main\Loader::includeModule('rest'))
{
	return;
}

/**
 * Class RestHandler
 * @package Bitrix\Sale\Delivery\Rest
 */
class HandlerService extends BaseService
{
	private const ERROR_HANDLER_ADD = 'ERROR_HANDLER_ADD';
	private const ERROR_HANDLER_UPDATE = 'ERROR_HANDLER_UPDATE';
	private const ERROR_HANDLER_DELETE = 'ERROR_HANDLER_DELETE';
	private const ERROR_CHECK_FAILURE = 'ERROR_CHECK_FAILURE';
	private const ERROR_HANDLER_ALREADY_EXIST = 'ERROR_HANDLER_ALREADY_EXIST';
	private const ERROR_HANDLER_NOT_FOUND = 'ERROR_HANDLER_NOT_FOUND';

	/**
	 * @param array $data
	 * @param \CRestServer $server
	 * @return array
	 */
	private static function prepareHandlerParams(array $data, \CRestServer $server): array
	{
		$data = self::prepareIncomingParams($data);
		$data['APP_ID'] = $server->getClientId();

		return $data;
	}

	/**
	 * @param array $params
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws RestException
	 */
	private static function checkParamsOnAddHandler(array $params): void
	{
		if (empty($params['NAME']))
		{
			throw new RestException('Parameter NAME is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (empty($params['CODE']))
		{
			throw new RestException('Parameter CODE is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (empty($params['SETTINGS']) || !is_array($params['SETTINGS']))
		{
			throw new RestException('Parameter SETTINGS is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (empty($params['SETTINGS']['CALCULATE_URL']))
		{
			throw new RestException('Parameter SETTINGS[CALCULATE_URL] is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (empty($params['SETTINGS']['CONFIG']) || !is_array($params['SETTINGS']['CONFIG']))
		{
			throw new RestException('Parameter SETTINGS[CONFIG] is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (empty($params['PROFILES']) || !is_array($params['PROFILES']))
		{
			throw new RestException('Parameter PROFILES is not defined', self::ERROR_CHECK_FAILURE);
		}

		$deliveryRestHandler = Internals\DeliveryRestHandlerTable::getList([
			'filter' => [
				'=CODE' => $params['CODE']
			]
		])->fetch();
		if ($deliveryRestHandler)
		{
			throw new RestException('Handler already exists!', self::ERROR_HANDLER_ALREADY_EXIST);
		}
	}

	/**
	 * @param array $params
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws RestException
	 */
	private static function checkParamsOnUpdateHandler(array $params): void
	{
		if (empty($params['ID']))
		{
			throw new RestException('Parameter ID is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (empty($params['FIELDS']) || !is_array($params['FIELDS']))
		{
			throw new RestException('Parameter FIELDS is not defined', self::ERROR_CHECK_FAILURE);
		}

		$deliveryRestHandler = Internals\DeliveryRestHandlerTable::getList(array(
			'filter' => array(
				'ID' => $params['ID']
			)
		))->fetch();
		if (!$deliveryRestHandler)
		{
			throw new RestException('Handler not found', self::ERROR_HANDLER_NOT_FOUND);
		}

		if ($params['APP_ID'] && !empty($deliveryRestHandler['APP_ID']) && $deliveryRestHandler['APP_ID'] !== $params['APP_ID'])
		{
			throw new RestException('Access denied', self::ERROR_HANDLER_UPDATE);
		}
	}

	/**
	 * @param $params
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws RestException
	 */
	private static function checkParamsOnDeleteHandler($params): void
	{
		if (empty($params['ID']))
		{
			throw new RestException('Parameter ID is not defined', self::ERROR_CHECK_FAILURE);
		}

		$deliveryRestHandler = Internals\DeliveryRestHandlerTable::getList(array(
			'filter' => array(
				'ID' => $params['ID']
			)
		))->fetch();
		if (!$deliveryRestHandler)
		{
			throw new RestException('Handler not found', self::ERROR_HANDLER_NOT_FOUND);
		}

		if ($params['APP_ID'] && !empty($deliveryRestHandler['APP_ID']) && $deliveryRestHandler['APP_ID'] !== $params['APP_ID'])
		{
			throw new RestException('Access denied', self::ERROR_HANDLER_DELETE);
		}

		$deliveryListResult = Sale\Delivery\Services\Manager::getList([
			'select' => ['ID', 'CLASS_NAME', 'CONFIG'],
			'filter' => [
				'=CLASS_NAME' => '\\'.\Sale\Handlers\Delivery\RestHandler::class,
			],
		]);
		$deliveryIdList = [];
		while ($delivery = $deliveryListResult->fetch())
		{
			if ($delivery['CONFIG']['MAIN']['REST_CODE'] === $deliveryRestHandler['CODE'])
			{
				$deliveryIdList[] = $delivery['ID'];
			}
		}

		if ($deliveryIdList)
		{
			throw new RestException(
				'There are deliveries with this handler: '.implode(', ', $deliveryIdList),
				self::ERROR_HANDLER_DELETE
			);
		}
	}

	/**
	 * @param $query
	 * @param $n
	 * @param \CRestServer $server
	 * @return array|false|int
	 * @throws AccessException
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws RestException
	 */
	public static function addHandler($query, $n, \CRestServer $server)
	{
		self::checkDeliveryPermission();
		$params = self::prepareHandlerParams($query, $server);
		self::checkParamsOnAddHandler($params);

		$data = [
			'NAME' => $params['NAME'],
			'CODE' => $params['CODE'],
			'SORT' => $params['SORT'] ?: 100,
			'DESCRIPTION' => $params['DESCRIPTION'],
			'SETTINGS' => $params['SETTINGS'],
			'PROFILES' => $params['PROFILES'],
			'APP_ID' => $params['APP_ID'],
		];

		$result = Internals\DeliveryRestHandlerTable::add($data);
		if ($result->isSuccess())
		{
			return $result->getId();
		}

		$error = implode("\n", $result->getErrorMessages());
		throw new RestException($error, self::ERROR_HANDLER_ADD);
	}

	/**
	 * @param $query
	 * @param $n
	 * @param \CRestServer $server
	 * @return bool
	 * @throws AccessException
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws RestException
	 */
	public static function updateHandler($query, $n, \CRestServer $server): bool
	{
		self::checkDeliveryPermission();
		$params = self::prepareHandlerParams($query, $server);

		self::checkParamsOnUpdateHandler($params);

		$result = Internals\DeliveryRestHandlerTable::update($params['ID'], $params['FIELDS']);
		if ($result->isSuccess())
		{
			return true;
		}

		$error = implode("\n", $result->getErrorMessages());
		throw new RestException($error, self::ERROR_HANDLER_UPDATE);
	}

	/**
	 * @param $query
	 * @param $n
	 * @param \CRestServer $server
	 * @return bool
	 * @throws AccessException
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws RestException
	 */
	public static function deleteHandler($query, $n, \CRestServer $server): bool
	{
		self::checkDeliveryPermission();
		$params = self::prepareHandlerParams($query, $server);

		self::checkParamsOnDeleteHandler($params);

		$result = Internals\DeliveryRestHandlerTable::delete($params['ID']);
		if ($result->isSuccess())
		{
			return true;
		}

		$error = implode("\n", $result->getErrorMessages());
		throw new RestException($error, self::ERROR_HANDLER_DELETE);
	}

	/**
	 * @param $query
	 * @param $n
	 * @param \CRestServer $server
	 * @return array
	 * @throws AccessException
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getHandlerList($query, $n, \CRestServer $server): array
	{
		self::checkDeliveryPermission();
		return Sale\Delivery\Services\Manager::getRestHandlerList() ?? [];
	}
}