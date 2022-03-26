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

		self::checkSettings($params['SETTINGS']);
		self::checkProfiles($params['PROFILES']);

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
	 * @throws RestException
	 * @throws AccessException
	 */
	private static function checkParamsOnUpdateHandler(array $params): void
	{
		if (empty($params['ID']))
		{
			throw new RestException('Parameter ID is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (array_key_exists('NAME', $params) && empty($params['NAME']))
		{
			throw new RestException('Parameter NAME is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (array_key_exists('CODE', $params) && empty($params['CODE']))
		{
			throw new RestException('Parameter CODE is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (array_key_exists('SETTINGS', $params))
		{
			self::checkSettings($params['SETTINGS']);
		}

		if (array_key_exists('PROFILES', $params))
		{
			self::checkProfiles($params['PROFILES']);
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
			throw new AccessException();
		}
	}

	/**
	 * @param $params
	 * @throws RestException
	 * @throws AccessException
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
			throw new AccessException();
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
	 * @throws RestException
	 */
	public static function addHandler($query, $n, \CRestServer $server)
	{
		self::checkDeliveryPermission();
		$params = self::prepareHandlerParams($query, $server);
		self::checkParamsOnAddHandler($params);

		if (isset($params['SETTINGS']['CONFIG']) && is_array($params['SETTINGS']['CONFIG']))
		{
			$params['SETTINGS']['CONFIG'] = [
				'ITEMS' => self::convertArrayForSaving($params['SETTINGS']['CONFIG'], '[SETTINGS][CONFIG][]'),
			];
		}

		$data = [
			'NAME' => $params['NAME'],
			'CODE' => $params['CODE'],
			'SORT' => $params['SORT'] ?: 100,
			'DESCRIPTION' => $params['DESCRIPTION'],
			'SETTINGS' => $params['SETTINGS'],
			'PROFILES' => self::convertArrayForSaving($params['PROFILES'], '[PROFILES][]'),
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
	 * @throws RestException
	 */
	public static function updateHandler($query, $n, \CRestServer $server): bool
	{
		self::checkDeliveryPermission();
		$params = self::prepareHandlerParams($query, $server);

		self::checkParamsOnUpdateHandler($params);

		$fields = [];
		foreach (['CODE', 'NAME', 'DESCRIPTION', 'SETTINGS', 'PROFILES'] as $field)
		{
			if (!array_key_exists($field, $params))
			{
				continue;
			}

			if ($field === 'PROFILES')
			{
				$value = self::convertArrayForSaving($params[$field], '[PROFILES][]');
			}
			elseif ($field === 'SETTINGS')
			{
				if (isset($params[$field]['CONFIG']) && is_array($params[$field]['CONFIG']))
				{
					$params[$field]['CONFIG'] = [
						'ITEMS' => self::convertArrayForSaving($params[$field]['CONFIG'], '[SETTINGS][CONFIG][]'),
					];
				}

				$value = $params[$field];
			}
			else
			{
				$value = $params[$field];
			}

			$fields[$field] = $value;
		}

		$result = Internals\DeliveryRestHandlerTable::update($params['ID'], $fields);
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
	 */
	public static function getHandlerList($query, $n, \CRestServer $server): array
	{
		self::checkDeliveryPermission();

		$result = [];

		$handlersList = array_values(Sale\Delivery\Services\Manager::getRestHandlerList());
		foreach ($handlersList as $handler)
		{
			/**
			 * Profiles
			 */
			$profiles = [];
			if (isset($handler['PROFILES']) && is_array($handler['PROFILES']))
			{
				$profiles = self::convertArrayForOutput($handler['PROFILES']);
			}
			$handler['PROFILES'] = $profiles;

			/**
			 * Settings
			 */
			$settings = $handler['SETTINGS'];
			$config = [];
			if (isset($settings['CONFIG']['ITEMS']) && is_array($settings['CONFIG']['ITEMS']))
			{
				$config = self::convertArrayForOutput($settings['CONFIG']['ITEMS']);
			}
			$settings['CONFIG'] = $config;
			$handler['SETTINGS'] = $settings;

			$result[] = $handler;
		}

		return $result;
	}

	/**
	 * @param $profiles
	 * @throws RestException
	 */
	private static function checkProfiles($profiles): void
	{
		if (empty($profiles) || !is_array($profiles))
		{
			throw new RestException('Parameter PROFILES is not defined', self::ERROR_CHECK_FAILURE);
		}
	}

	/**
	 * @param $settings
	 * @throws RestException
	 */
	private static function checkSettings($settings): void
	{
		if (empty($settings) || !is_array($settings))
		{
			throw new RestException('Parameter SETTINGS is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (empty($settings['CALCULATE_URL']))
		{
			throw new RestException(
				'Parameter SETTINGS[CALCULATE_URL] is not defined',
				self::ERROR_CHECK_FAILURE
			);
		}
		elseif (!is_string($settings['CALCULATE_URL']))
		{
			throw new RestException(
				'Parameter SETTINGS[CALCULATE_URL] must be of string type',
				self::ERROR_CHECK_FAILURE
			);
		}

		if (
			!empty($settings['CREATE_DELIVERY_REQUEST_URL'])
			&& !is_string($settings['CREATE_DELIVERY_REQUEST_URL'])
		)
		{
			throw new RestException(
				'Parameter SETTINGS[CREATE_DELIVERY_REQUEST_URL] must be of string type',
				self::ERROR_CHECK_FAILURE
			);
		}

		if (
			!empty($settings['CANCEL_DELIVERY_REQUEST_URL'])
			&& !is_string($settings['CANCEL_DELIVERY_REQUEST_URL'])
		)
		{
			throw new RestException(
				'Parameter SETTINGS[CANCEL_DELIVERY_REQUEST_URL] must be of string type',
				self::ERROR_CHECK_FAILURE
			);
		}

		if (empty($settings['CONFIG']) || !is_array($settings['CONFIG']))
		{
			throw new RestException('Parameter SETTINGS[CONFIG] is not defined', self::ERROR_CHECK_FAILURE);
		}
	}

	/**
	 * @param array $items
	 * @param string $path
	 * @return array
	 * @throws RestException
	 */
	private static function convertArrayForSaving(array $items, string $path = ''): array
	{
		$result = [];

		foreach ($items as $item)
		{
			if (!isset($item['CODE']))
			{
				throw new RestException(
					($path === '' ? '' : $path . ' ') . 'Item CODE is not specified',
					self::ERROR_CHECK_FAILURE
				);
			}

			$profileCode = $item['CODE'];
			unset($item['CODE']);

			$result[$profileCode] = $item;
		}

		return $result;
	}

	/**
	 * @param array $items
	 * @return array
	 */
	private static function convertArrayForOutput(array $items): array
	{
		$result = [];

		foreach ($items as $profileCode => $item)
		{
			$item['CODE'] = $profileCode;

			$result[] = $item;
		}

		return $result;
	}
}
