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

	private const ENUM_TYPE = 'enum';
	private const CHECKBOX_TYPE = 'checkbox';
	private const QUANTITY_TYPE = 'quantity';

	/**
	 * @param $query
	 * @param $n
	 * @param \CRestServer $server
	 * @return array|int
	 * @throws RestException
	 */
	public static function addExtraServices($query, $n, \CRestServer $server)
	{
		self::checkDeliveryPermission();
		$params = self::prepareExtraServicesParams($query, $server);

		if (empty($params['DELIVERY_ID']))
		{
			throw new RestException('Parameter DELIVERY_ID is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (empty($params['NAME']))
		{
			throw new RestException('Parameter NAME is not defined', self::ERROR_CHECK_FAILURE);
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

		if ($params['TYPE'] === self::ENUM_TYPE)
		{
			if (!isset($params['ITEMS']) || !is_array($params['ITEMS']) || empty($params['ITEMS']))
			{
				throw new RestException('Parameter ITEMS must be defined for enum type', self::ERROR_CHECK_FAILURE);
			}
		}

		$deliveryService = Sale\Delivery\Services\Manager::getById($params['DELIVERY_ID']);
		if ($deliveryService)
		{
			if (!self::hasAccessToDelivery($deliveryService, $params['APP_ID']))
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

		$fields = Sale\Delivery\ExtraServices\Manager::prepareParamsToSave([
			'CLASS_NAME' => $extraServicesTypeMap[$params['TYPE']],
			'DELIVERY_ID' => $params['DELIVERY_ID'],
			'NAME' => $params['NAME'],
			'ACTIVE' => (!empty($params['ACTIVE']) && $params['ACTIVE'] === 'Y') ? 'Y' : 'N',
			'CODE' => $params['CODE'] ?? '',
			'DESCRIPTION' => $params['DESCRIPTION'] ?? '',
			'PARAMS' => self::makeInternalParams($params['TYPE'], $params),
			'SORT' => $params['SORT'] ? (int)$params['SORT'] : 100,
			'RIGHTS' => [
				Sale\Delivery\ExtraServices\Manager::RIGHTS_ADMIN_IDX => 'Y',
				Sale\Delivery\ExtraServices\Manager::RIGHTS_MANAGER_IDX => 'Y',
				Sale\Delivery\ExtraServices\Manager::RIGHTS_CLIENT_IDX => 'Y',
			],
		]);
		$result = Sale\Delivery\ExtraServices\Table::add($fields);
		if ($result->isSuccess())
		{
			return $result->getId();
		}

		$error = implode("\n", $result->getErrorMessages());
		throw new RestException($error, self::ERROR_EXTRA_SERVICE_ADD);
	}

	/**
	 * @param $query
	 * @param $n
	 * @param \CRestServer $server
	 * @return bool
	 * @throws RestException
	 */
	public static function updateExtraServices($query, $n, \CRestServer $server)
	{
		self::checkDeliveryPermission();
		$params = self::prepareExtraServicesParams($query, $server);

		if (empty($params['ID']))
		{
			throw new RestException('Parameter ID is not defined', self::ERROR_CHECK_FAILURE);
		}

		$extraService = Sale\Delivery\ExtraServices\Table::getList([
			'select' => ['ID', 'CODE', 'DELIVERY_ID', 'CLASS_NAME'],
			'filter' => [
				'=ID' => $params['ID'],
			],
			'limit' => 1,
		])->fetch();
		if ($extraService)
		{
			$deliveryService = Sale\Delivery\Services\Manager::getById($extraService['DELIVERY_ID']);
			if ($deliveryService && !self::hasAccessToDelivery($deliveryService, $params['APP_ID']))
			{
				throw new AccessException();
			}

			$newCode = $params['CODE'] ?? '';
			if ($newCode)
			{
				$existingExtraService = Sale\Delivery\ExtraServices\Table::getList([
					'select' => ['ID'],
					'filter' => [
						'=DELIVERY_ID' => $extraService['DELIVERY_ID'],
						'=CODE' => $newCode,
					],
					'limit' => 1,
				])->fetch();
				if ($existingExtraService && (int)$existingExtraService['ID'] !== (int)$params['ID'])
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

		$extraServicesTypeMap = array_flip(self::getExtraServicesTypeMap());

		if (!isset($extraServicesTypeMap[$extraService['CLASS_NAME']]))
		{
			throw new RestException('Unknown type', self::ERROR_CHECK_FAILURE);
		}
		$type = $extraServicesTypeMap[$extraService['CLASS_NAME']];

		$fields = [];

		if (array_key_exists('NAME', $params))
		{
			if (empty($params['NAME']))
			{
				throw new RestException('Parameter NAME should not be empty', self::ERROR_CHECK_FAILURE);
			}

			$fields['NAME'] = (string)$params['NAME'];
		}

		if (array_key_exists('ACTIVE', $params))
		{
			$fields['ACTIVE'] = $params['ACTIVE'] === 'Y' ? 'Y' : 'N';
		}

		if (array_key_exists('DESCRIPTION', $params))
		{
			$fields['DESCRIPTION'] = (string)$params['DESCRIPTION'];
		}

		if (array_key_exists('SORT', $params))
		{
			$fields['SORT'] = (int)$params['SORT'];
		}

		if (array_key_exists('CODE', $params))
		{
			$fields['CODE'] = (string)$params['CODE'];
		}

		$internalParams = self::makeInternalParams($type, $params);
		if (!is_null($internalParams))
		{
			$fields['PARAMS'] = $internalParams;
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

		$result = Sale\Delivery\ExtraServices\Table::delete($params['ID']);
		if ($result->isSuccess())
		{
			return true;
		}

		$error = implode("\n", $result->getErrorMessages());
		throw new RestException($error, self::ERROR_EXTRA_SERVICE_DELETE);
	}

	/**
	 * @param $query
	 * @param $n
	 * @param \CRestServer $server
	 * @return array
	 */
	public static function get($query, $n, \CRestServer $server): array
	{
		self::checkDeliveryPermission();
		$params = self::prepareIncomingParams($query);

		if (empty($params['DELIVERY_ID']))
		{
			throw new RestException('Parameter DELIVERY_ID is not defined', self::ERROR_CHECK_FAILURE);
		}

		$data = Sale\Delivery\Services\Manager::getById($params['DELIVERY_ID']);
		if (!$data)
		{
			throw new RestException('Delivery not found', self::ERROR_DELIVERY_NOT_FOUND);
		}

		$esClasses = Sale\Delivery\ExtraServices\Manager::getClassesList();

		$extraServices = Sale\Delivery\ExtraServices\Table::getList([
			'filter' => [
				'=DELIVERY_ID' => $params['DELIVERY_ID'],
				'=CLASS_NAME' => $esClasses
			],
			'select' => ['ID', 'CODE', 'NAME', 'DESCRIPTION', 'CLASS_NAME', 'ACTIVE', 'SORT', 'PARAMS'],
			'order' => [
				'SORT' => 'ASC',
			],
		])->fetchAll();

		$extraServicesTypeMap = array_flip(self::getExtraServicesTypeMap());

		foreach ($extraServices as $key => $extraService)
		{
			$type = $extraServicesTypeMap[$extraService['CLASS_NAME']];
			$extraServices[$key]['TYPE'] = $type;
			unset($extraServices[$key]['CLASS_NAME']);

			if ($type === self::ENUM_TYPE)
			{
				if (array_key_exists('PRICES', $extraServices[$key]['PARAMS']))
				{
					if (is_array($extraServices[$key]['PARAMS']['PRICES']))
					{
						$extraServices[$key]['ITEMS'] = [];
						foreach ($extraServices[$key]['PARAMS']['PRICES'] as $price)
						{
							$extraServices[$key]['ITEMS'][] = [
								'TITLE' => $price['TITLE'],
								'CODE' => $price['CODE'],
								'PRICE' => $price['PRICE'] ?? null,
							];
						}
					}
				}
			}
			elseif (in_array($type, [self::CHECKBOX_TYPE, self::QUANTITY_TYPE]))
			{
				if (array_key_exists('PRICE', $extraServices[$key]['PARAMS']))
				{
					$extraServices[$key]['PRICE'] = $extraServices[$key]['PARAMS']['PRICE'];
				}
			}
			unset($extraServices[$key]['PARAMS']);
		}

		return $extraServices ?: [];
	}

	/**
	 * @param string $type
	 * @param array $params
	 * @return array|null
	 * @throws RestException
	 */
	private static function makeInternalParams(string $type, array $params): ?array
	{
		$result = null;

		if ($type === self::ENUM_TYPE)
		{
			if (is_array($params['ITEMS']))
			{
				$result = [
					'PRICES' => [],
				];

				foreach ($params['ITEMS'] as $item)
				{
					if (!isset($item['TITLE']) || empty($item['TITLE']))
					{
						throw new RestException('Item title must be defined for enum item', self::ERROR_CHECK_FAILURE);
					}

					if (!isset($item['CODE']) || empty($item['CODE']))
					{
						throw new RestException('Item code must be defined for enum item', self::ERROR_CHECK_FAILURE);
					}

					$result['PRICES'][$item['CODE']] = [
						'TITLE' => $item['TITLE'],
						'CODE' => $item['CODE'],
						'PRICE' => isset($item['PRICE']) ? (float)$item['PRICE'] : null,
					];
				}
			}
		}
		elseif (in_array($type, [self::CHECKBOX_TYPE, self::QUANTITY_TYPE]) && array_key_exists('PRICE', $params))
		{
			$result['PRICE'] = isset($params['PRICE']) ? (float)$params['PRICE'] : null;
		}

		return $result;
	}

	/**
	 * @return array
	 * @throws \ReflectionException
	 */
	private static function getExtraServicesTypeMap(): array
	{
		$result = [];

		$allowedTypes = [
			self::ENUM_TYPE,
			self::CHECKBOX_TYPE,
			self::QUANTITY_TYPE,
		];
		$esClasses = Sale\Delivery\ExtraServices\Manager::getClassesList();
		foreach ($esClasses as $className)
		{
			$classCode = (new \ReflectionClass($className))->getShortName();
			$type = mb_strtolower($classCode);
			if (!in_array($type, $allowedTypes, true))
			{
				continue;
			}
			$result[$type] = $className;
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
}
