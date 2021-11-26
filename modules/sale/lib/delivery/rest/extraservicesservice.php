<?php

namespace Bitrix\Sale\Delivery\Rest;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Rest\RestException;
use Bitrix\Rest\AccessException;

if (!Main\Loader::includeModule('rest'))
{
	return;
}

/**
 * Class ExtraServicesService
 * @package Bitrix\Sale\Delivery\Rest
 */
class ExtraServicesService extends BaseService
{
	private const ERROR_EXTRA_SERVICE_ADD = 'ERROR_EXTRA_SERVICE_ADD';
	private const ERROR_EXTRA_SERVICE_UPDATE = 'ERROR_EXTRA_SERVICE_UPDATE';
	private const ERROR_EXTRA_SERVICE_DELETE = 'ERROR_EXTRA_SERVICE_DELETE';

	private const ERROR_CHECK_FAILURE = 'ERROR_CHECK_FAILURE';
	private const ERROR_EXTRA_SERVICE_NOT_FOUND = 'ERROR_EXTRA_SERVICE_NOT_FOUND';
	private const ERROR_DELIVERY_NOT_FOUND = 'ERROR_DELIVERY_NOT_FOUND';

	private static function getExtraServicesTypeMap(): array
	{
		$result = [];

		$esClasses = Sale\Delivery\ExtraServices\Manager::getClassesList();
		foreach ($esClasses as $className)
		{
			$classCode = (new \ReflectionClass($className))->getShortName();
			$result[mb_strtolower($classCode)] = $className;
		}

		return $result;
	}

	/**
	 * @param array $data
	 * @param \CRestServer $server
	 * @return array
	 */
	private static function prepareExtraServicesParams(array $data, \CRestServer $server): array
	{
		$data = self::prepareIncomingParams($data);
		$data['APP_ID'] = $server->getClientId();

		return $data;
	}

	/**
	 * @param $query
	 * @param $n
	 * @param \CRestServer $server
	 * @return array|int
	 */
	public static function addExtraServices($query, $n, \CRestServer $server)
	{
		self::checkDeliveryPermission();
		$params = self::prepareExtraServicesParams($query, $server);
		self::checkParamsBeforeAddExtraServices($params);

		$extraServicesTypeMap = self::getExtraServicesTypeMap();
		$fields = [
			'CLASS_NAME' => $extraServicesTypeMap[$params['TYPE']],
			'DELIVERY_ID' => $params['DELIVERY_ID'],
			'NAME' => $params['NAME'],
			'ACTIVE' => (!empty($params['ACTIVE']) && $params['ACTIVE'] === 'Y') ? 'Y' : 'N',
			'CODE' => $params['CODE'] ?? '',
			'DESCRIPTION' => $params['DESCRIPTION'] ?? '',
			'PARAMS' => $params['PARAMS'],
			'SORT' => $params['SORT'] ? (int)$params['SORT'] : 100,
			'RIGHTS' => $params['RIGHTS'],
		];

		$fields = Sale\Delivery\ExtraServices\Manager::prepareParamsToSave($fields);

		$result = Sale\Delivery\ExtraServices\Table::add($fields);
		if ($result->isSuccess())
		{
			return $result->getId();
		}

		$error = implode("\n", $result->getErrorMessages());
		throw new RestException($error, self::ERROR_EXTRA_SERVICE_ADD);
	}

	private static function checkParamsBeforeAddExtraServices($params)
	{
		if (empty($params['DELIVERY_ID']))
		{
			throw new RestException('Parameter DELIVERY_ID is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (empty($params['NAME']))
		{
			throw new RestException('Parameter NAME is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (empty($params['RIGHTS']))
		{
			throw new RestException('Parameter RIGHTS is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (empty($params['PARAMS']) || !is_array($params['PARAMS']))
		{
			throw new RestException('Parameter PARAMS is not defined', self::ERROR_CHECK_FAILURE);
		}

		$extraServiceType = $params['TYPE'] ?? null;
		if (!$extraServiceType)
		{
			throw new RestException('Parameter TYPE is not defined', self::ERROR_CHECK_FAILURE);
		}

		$extraServicesTypeMap = self::getExtraServicesTypeMap();
		if (empty($extraServicesTypeMap[$extraServiceType]))
		{
			throw new RestException('Parameter TYPE is unknown', self::ERROR_CHECK_FAILURE);
		}

		$data = Sale\Delivery\Services\Manager::getById($params['DELIVERY_ID']);
		if ($data)
		{
			if (!self::hasAccessToDelivery($data, $params['APP_ID']))
			{
				throw new AccessException();
			}
		}
		else
		{
			throw new RestException('Delivery not found', self::ERROR_DELIVERY_NOT_FOUND);
		}

		if (!empty($params['CODE']))
		{
			$extraServiceData = Sale\Delivery\ExtraServices\Table::getList([
				'select' => ['ID'],
				'filter' => [
					'=DELIVERY_ID' => $params['DELIVERY_ID'],
					'=CODE' => $params['CODE'],
				],
				'limit' => 1,
			])->fetch();
			if ($extraServiceData)
			{
				throw new RestException(
					'CODE "' . $params['CODE'] . '" already exists for this delivery',
					self::ERROR_CHECK_FAILURE
				);
			}
		}
	}

	/**
	 * @param $query
	 * @param $n
	 * @param \CRestServer $server
	 * @return array|int
	 */
	public static function updateExtraServices($query, $n, \CRestServer $server)
	{
		self::checkDeliveryPermission();
		$params = self::prepareExtraServicesParams($query, $server);
		self::checkParamsBeforeUpdateExtraServices($params);

		$fields = [];

		if (!empty($params['FIELDS']['TYPE']))
		{
			$extraServicesTypeMap = self::getExtraServicesTypeMap();
			$fields['CLASS_NAME'] = $extraServicesTypeMap[$params['FIELDS']['TYPE']];
		}

		if (!empty($params['FIELDS']['NAME']))
		{
			$fields['NAME'] = $params['FIELDS']['NAME'];
		}

		if (!empty($params['FIELDS']['ACTIVE']))
		{
			$fields['ACTIVE'] = $params['FIELDS']['ACTIVE'] === 'Y' ? 'Y' : 'N';
		}

		if (isset($params['FIELDS']['DESCRIPTION']))
		{
			$fields['DESCRIPTION'] = $params['FIELDS']['DESCRIPTION'];
		}

		if (!empty($params['FIELDS']['SORT']))
		{
			$fields['SORT'] = (int)$params['FIELDS']['SORT'];
		}

		if (isset($params['FIELDS']['CODE']))
		{
			$fields['CODE'] = $params['FIELDS']['CODE'];
		}

		if (!empty($params['FIELDS']['PARAMS']))
		{
			$fields['PARAMS'] = $params['FIELDS']['PARAMS'];
		}

		if (!empty($params['FIELDS']['RIGHTS']))
		{
			$fields['RIGHTS'] = $params['FIELDS']['RIGHTS'];
		}

		$fields = Sale\Delivery\ExtraServices\Manager::prepareParamsToSave($fields);

		$result = Sale\Delivery\ExtraServices\Table::update($params['ID'], $fields);
		if ($result->isSuccess())
		{
			return true;
		}

		$error = implode("\n", $result->getErrorMessages());
		throw new RestException($error, self::ERROR_EXTRA_SERVICE_UPDATE);
	}

	private static function checkParamsBeforeUpdateExtraServices($params)
	{
		if (empty($params['ID']))
		{
			throw new RestException('Parameter ID is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (empty($params['FIELDS']) || !is_array($params['FIELDS']))
		{
			throw new RestException('Parameter FIELDS is not defined', self::ERROR_CHECK_FAILURE);
		}

		$extraServiceType = $params['FIELDS']['TYPE'] ?? null;
		if ($extraServiceType)
		{
			$extraServicesTypeMap = self::getExtraServicesTypeMap();
			if (empty($extraServicesTypeMap[$extraServiceType]))
			{
				throw new RestException('Parameter FIELDS[TYPE] is unknown', self::ERROR_CHECK_FAILURE);
			}
		}

		if (!empty($params['FIELDS']['PARAMS']) && !is_array($params['FIELDS']['PARAMS']))
		{
			throw new RestException('Parameter FIELDS[PARAMS] is not defined', self::ERROR_CHECK_FAILURE);
		}

		$extraServiceData = Sale\Delivery\ExtraServices\Table::getList([
			'select' => ['ID', 'CODE', 'DELIVERY_ID'],
			'filter' => [
				'=ID' => $params['ID'],
			],
			'limit' => 1,
		])->fetch();
		if ($extraServiceData)
		{
			$data = Sale\Delivery\Services\Manager::getById($extraServiceData['DELIVERY_ID']);
			if ($data && !self::hasAccessToDelivery($data, $params['APP_ID']))
			{
				throw new AccessException();
			}

			$newCode = $params['FIELDS']['CODE'] ?? '';
			if ($newCode)
			{
				$extraServiceData = Sale\Delivery\ExtraServices\Table::getList([
					'select' => ['ID'],
					'filter' => [
						'=DELIVERY_ID' => $extraServiceData['DELIVERY_ID'],
						'=CODE' => $newCode,
					],
					'limit' => 1,
				])->fetch();
				if ($extraServiceData && (int)$extraServiceData['ID'] !== (int)$params['ID'])
				{
					throw new RestException(
						'FIELDS[CODE] "' . $newCode . '" already exists for this delivery',
						self::ERROR_CHECK_FAILURE
					);
				}
			}
		}
		else
		{
			throw new RestException('Extra service not found', self::ERROR_EXTRA_SERVICE_NOT_FOUND);
		}
	}

	/**
	 * @param $query
	 * @param $n
	 * @param \CRestServer $server
	 * @return bool
	 */
	public static function deleteExtraServices($query, $n, \CRestServer $server)
	{
		self::checkDeliveryPermission();
		$params = self::prepareExtraServicesParams($query, $server);
		self::checkParamsBeforeDeleteExtraServices($params);

		$result = Sale\Delivery\ExtraServices\Table::delete($params['ID']);
		if ($result->isSuccess())
		{
			return true;
		}

		$error = implode("\n", $result->getErrorMessages());
		throw new RestException($error, self::ERROR_EXTRA_SERVICE_DELETE);
	}

	private static function checkParamsBeforeDeleteExtraServices(array $params)
	{
		if (empty($params['ID']))
		{
			throw new RestException('Parameter ID is not defined', self::ERROR_CHECK_FAILURE);
		}

		$extraService = Sale\Delivery\ExtraServices\Table::getById($params['ID'])->fetch();
		if ($extraService)
		{
			$data = Sale\Delivery\Services\Manager::getById($extraService['DELIVERY_ID']);
			if ($data && !self::hasAccessToDelivery($data, $params['APP_ID']))
			{
				throw new AccessException();
			}
		}
		else
		{
			throw new RestException('Extra service not found', self::ERROR_EXTRA_SERVICE_NOT_FOUND);
		}
	}

	/**
	 * @param $query
	 * @param $n
	 * @param \CRestServer $server
	 * @return array
	 */
	public static function getExtraServiceByDeliveryId($query, $n, \CRestServer $server): array
	{
		self::checkDeliveryPermission();
		$params = self::prepareIncomingParams($query);
		self::checkParamsBeforeGetExtraServicesList($params);

		$esClasses = Sale\Delivery\ExtraServices\Manager::getClassesList();

		$extraServices = Sale\Delivery\ExtraServices\Table::getList([
			'filter' => [
				'=DELIVERY_ID' => $params['ID'],
				'=CLASS_NAME' => $esClasses
			],
			'select' => ['ID', 'CODE', 'NAME', 'DESCRIPTION', 'CLASS_NAME', 'PARAMS', 'RIGHTS', 'ACTIVE', 'SORT'],
			'order' => [
				'SORT' => 'ASC',
			],
		])->fetchAll();

		$extraServicesTypeMap = array_flip(self::getExtraServicesTypeMap());

		foreach ($extraServices as $key => $extraService)
		{
			$extraServices[$key]['TYPE'] = $extraServicesTypeMap[$extraService['CLASS_NAME']];
			unset($extraServices[$key]['CLASS_NAME']);
		}

		return $extraServices ?: [];
	}

	private static function checkParamsBeforeGetExtraServicesList(array $params)
	{
		if (empty($params['ID']))
		{
			throw new RestException('Parameter ID is not defined', self::ERROR_CHECK_FAILURE);
		}

		$data = Sale\Delivery\Services\Manager::getById($params['ID']);
		if (!$data)
		{
			throw new RestException('Delivery not found', self::ERROR_DELIVERY_NOT_FOUND);
		}
	}

	/**
	 * @param $query
	 * @param $n
	 * @param \CRestServer $server
	 * @return array
	 */
	public static function getExtraServicesTypeList($query, $n, \CRestServer $server): array
	{
		self::checkDeliveryPermission();

		return array_keys(self::getExtraServicesTypeMap());
	}
}