<?php

namespace Bitrix\Sale\Delivery\Rest;

use Bitrix\Main,
	Bitrix\Sale\Delivery,
	Bitrix\Rest\AccessException,
	Bitrix\Rest\RestException;

if (!Main\Loader::includeModule('rest'))
{
	return;
}

/**
 * Class DeliveryManager
 * @package Bitrix\Sale\Delivery\Rest
 */
class DeliveryService extends BaseService
{
	private const ERROR_CHECK_FAILURE = 'ERROR_CHECK_FAILURE';
	private const ERROR_HANDLER_NOT_FOUND = 'ERROR_HANDLER_NOT_FOUND';
	private const ERROR_DELIVERY_ADD = 'ERROR_DELIVERY_ADD';
	private const ERROR_DELIVERY_UPDATE = 'ERROR_DELIVERY_UPDATE';
	private const ERROR_DELIVERY_DELETE = 'ERROR_DELIVERY_DELETE';
	private const ERROR_DELIVERY_CONFIG_UPDATE = 'ERROR_DELIVERY_CONFIG_UPDATE';
	private const ERROR_DELIVERY_NOT_FOUND = 'ERROR_DELIVERY_NOT_FOUND';

	/**
	 * @param $params
	 * @throws Main\SystemException
	 * @throws RestException
	 */
	private static function checkParamsBeforeDeliveryAdd($params): void
	{
		if (empty($params['NAME']))
		{
			throw new RestException('Parameter NAME is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (empty($params['CLASS_NAME']))
		{
			throw new RestException('Parameter CLASS_NAME is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (empty($params['CONFIG']) || !is_array($params['CONFIG']))
		{
			throw new RestException('Parameter CONFIG is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (empty($params['CURRENCY']))
		{
			throw new RestException('Parameter CURRENCY is not defined', self::ERROR_CHECK_FAILURE);
		}

		$handlerList = Delivery\Services\Manager::getHandlersList();
		if (!in_array($params['CLASS_NAME'], $handlerList, true))
		{
			throw new RestException('Handler not found', self::ERROR_HANDLER_NOT_FOUND);
		}
	}

	/**
	 * @param $params
	 * @throws Main\SystemException
	 * @throws RestException
	 */
	private static function checkParamsBeforeDeliveryUpdate($params): void
	{
		if (empty($params['ID']))
		{
			throw new RestException('Parameter ID is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (empty($params['FIELDS']))
		{
			throw new RestException('Parameter FIELDS is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (isset($params['FIELDS']['CLASS_NAME']))
		{
			$handlerList = Delivery\Services\Manager::getHandlersList();
			if (!in_array($params['FIELDS']['CLASS_NAME'], $handlerList, true))
			{
				throw new RestException('Handler not found', self::ERROR_HANDLER_NOT_FOUND);
			}
		}

		$data = Delivery\Services\Manager::getById($params['ID']);
		if (!$data)
		{
			throw new RestException('Delivery not found', self::ERROR_DELIVERY_NOT_FOUND);
		}
	}

	/**
	 * @param $params
	 * @throws Main\SystemException
	 * @throws RestException
	 */
	private static function checkParamsBeforeDeliveryDelete($params): void
	{
		if (empty($params['ID']))
		{
			throw new RestException('Parameter ID is not defined', self::ERROR_CHECK_FAILURE);
		}

		$data = Delivery\Services\Manager::getById($params['ID']);
		if (!$data)
		{
			throw new RestException('Delivery not found', self::ERROR_DELIVERY_NOT_FOUND);
		}
	}

	/**
	 * @param $params
	 * @throws RestException
	 */
	private static function checkParamsBeforeDeliveryConfigGet($params): void
	{
		if (empty($params['ID']))
		{
			throw new RestException('Parameter ID is not defined', self::ERROR_CHECK_FAILURE);
		}
	}

	private static function checkParamsBeforeDeliveryConfigUpdate($params): void
	{
		if (empty($params['ID']))
		{
			throw new RestException('Parameter ID is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (empty($params['FIELDS']) || !is_array($params['FIELDS']))
		{
			throw new RestException('Parameter FIELDS is not defined', self::ERROR_CHECK_FAILURE);
		}

		$data = Delivery\Services\Manager::getById($params['ID']);
		if (!$data)
		{
			throw new RestException('Delivery not found', self::ERROR_DELIVERY_NOT_FOUND);
		}
	}

	private static function saveFile($fileContent)
	{
		$file = \CRestUtil::saveFile($fileContent);
		if ($file)
		{
			$file['MODULE_ID'] = 'sale';
			return \CFile::SaveFile($file, 'sale');
		}

		return null;
	}

	/**
	 * @param $query
	 * @param $n
	 * @param \CRestServer $server
	 * @return array|int
	 * @throws AccessException
	 * @throws Main\LoaderException
	 * @throws Main\SystemException
	 * @throws RestException
	 */
	public static function addDelivery($query, $n, \CRestServer $server)
	{
		self::checkDeliveryPermission();
		$params = self::prepareParams($query);
		self::checkParamsBeforeDeliveryAdd($params);

		$fields = [
			'NAME' => $params['NAME'],
			'DESCRIPTION' => $params['DESCRIPTION'] ?? '',
			'CLASS_NAME' => $params['CLASS_NAME'],
			'CURRENCY' => $params['CURRENCY'],
			'SORT' => $params['SORT'] ?? 100,
			'ACTIVE' => $params['ACTIVE'] ?? 'Y',
			'CONFIG' => $params['CONFIG'],
		];

		if (isset($params['LOGOTIP']))
		{
			$fields['LOGOTIP'] = self::saveFile($params['LOGOTIP']);
		}

		$result = Delivery\Services\Manager::add($fields);
		if ($result->isSuccess())
		{
			return $result->getId();
		}

		$error = implode("\n", $result->getErrorMessages());
		throw new RestException($error, self::ERROR_DELIVERY_ADD);
	}

	/**
	 * @param $query
	 * @param $n
	 * @param \CRestServer $server
	 * @return bool
	 * @throws AccessException
	 * @throws Main\LoaderException
	 * @throws Main\SystemException
	 * @throws RestException
	 */
	public static function updateDelivery($query, $n, \CRestServer $server): bool
	{
		self::checkDeliveryPermission();
		$params = self::prepareParams($query);
		self::checkParamsBeforeDeliveryUpdate($params);

		$fields = array();
		if (isset($params['FIELDS']['NAME']))
		{
			$fields['NAME'] = $params['FIELDS']['NAME'];
		}

		if (isset($params['FIELDS']['ACTIVE']))
		{
			$fields['ACTIVE'] = $params['FIELDS']['ACTIVE'];
		}

		if (isset($params['FIELDS']['DESCRIPTION']))
		{
			$fields['DESCRIPTION'] = $params['FIELDS']['DESCRIPTION'];
		}

		if (isset($params['FIELDS']['SORT']))
		{
			$fields['SORT'] = $params['FIELDS']['SORT'];
		}

		if (isset($params['FIELDS']['CURRENCY']))
		{
			$fields['CURRENCY'] = $params['FIELDS']['CURRENCY'];
		}

		if (isset($params['FIELDS']['LOGOTIP']))
		{
			$fields['LOGOTIP'] = self::saveFile($params['FIELDS']['LOGOTIP']);
		}

		$result = Delivery\Services\Manager::update($params['ID'], $fields);
		if ($result->isSuccess())
		{
			return true;
		}

		$error = implode("\n", $result->getErrorMessages());
		throw new RestException($error, self::ERROR_DELIVERY_UPDATE);
	}

	/**
	 * @param $query
	 * @param $n
	 * @param \CRestServer $server
	 * @return bool
	 * @throws AccessException
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\LoaderException
	 * @throws Main\SystemException
	 * @throws RestException
	 */
	public static function deleteDelivery($query, $n, \CRestServer $server): bool
	{
		self::checkDeliveryPermission();
		$params = self::prepareParams($query);
		self::checkParamsBeforeDeliveryDelete($params);

		$result = Delivery\Services\Manager::delete($params['ID']);
		if ($result->isSuccess())
		{
			return true;
		}

		$error = implode("\n", $result->getErrorMessages());
		throw new RestException($error, self::ERROR_DELIVERY_DELETE);
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
	public static function getDeliveryList($query, $n, \CRestServer $server): array
	{
		self::checkDeliveryPermission();

		$select = isset($query['select']) && is_array($query['select']) ? $query['select'] : ['*'];
		$filter = isset($query['filter']) && is_array($query['filter']) ? self::prepareParams($query['filter']) : [];
		$order = isset($query['order']) && is_array($query['order']) ? self::prepareParams($query['order']) : [];

		$result = array();
		$deliveryListResult = Delivery\Services\Manager::getList([
			'select' => $select,
			'filter' => $filter,
			'order' => $order,
		]);
		while ($delivery = $deliveryListResult->fetch())
		{
			$result[] = $delivery;
		}

		return $result;
	}

	/**
	 * @param $query
	 * @param $n
	 * @param \CRestServer $server
	 * @return array
	 * @throws AccessException
	 * @throws Main\LoaderException
	 * @throws Main\SystemException
	 * @throws RestException
	 */
	public static function getConfig($query, $n, \CRestServer $server): array
	{
		self::checkDeliveryPermission();
		$params = self::prepareParams($query);
		self::checkParamsBeforeDeliveryConfigGet($params);

		$delivery = Delivery\Services\Manager::getById($params['ID']);
		if ($delivery)
		{
			return $delivery['CONFIG'] ?: [];
		}

		throw new RestException('Delivery not found', self::ERROR_DELIVERY_NOT_FOUND);
	}

	/**
	 * @param $query
	 * @param $n
	 * @param \CRestServer $server
	 * @return bool
	 * @throws AccessException
	 * @throws Main\LoaderException
	 * @throws Main\SystemException
	 * @throws RestException
	 */
	public static function updateConfig($query, $n, \CRestServer $server): bool
	{
		self::checkDeliveryPermission();
		$params = self::prepareParams($query);
		self::checkParamsBeforeDeliveryConfigUpdate($params);

		$result = Delivery\Services\Manager::update($params['ID'], ['CONFIG' => $params['FIELDS']]);
		if ($result->isSuccess())
		{
			return true;
		}

		$error = implode("\n", $result->getErrorMessages());
		throw new RestException($error, self::ERROR_DELIVERY_CONFIG_UPDATE);
	}
}