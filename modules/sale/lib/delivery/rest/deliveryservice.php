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

	private const ALLOWED_DELIVERY_FIELDS = [
		'ID',
		'PARENT_ID',
		'NAME',
		'ACTIVE',
		'DESCRIPTION',
		'SORT',
		'LOGOTIP',
		'CURRENCY',
	];

	private const DELIVERY_FIELDS_STRING_TYPE = [
		'NAME',
		'ACTIVE',
		'DESCRIPTION',
		'CURRENCY',
	];

	private const DELIVERY_FIELDS_INT_TYPE = [
		'ID',
		'PARENT_ID',
		'SORT',
		'LOGOTIP',
	];

	/**
	 * @param array $data
	 * @param \CRestServer $server
	 * @return array
	 */
	private static function prepareDeliveryParams(array $data, \CRestServer $server): array
	{
		$data = self::prepareIncomingParams($data);
		$data['APP_ID'] = $server->getClientId();

		return $data;
	}

	/**
	 * @return string[]
	 */
	protected static function getIncomingFieldsMap(): array
	{
		return [
			'LOGOTYPE' => 'LOGOTIP',
		];
	}

	/**
	 * @return string[]
	 */
	protected static function getOutcomingFieldsMap(): array
	{
		return [
			'LOGOTIP' => 'LOGOTYPE',
		];
	}

	protected static function castOutcomingFieldsData(array $data): array
	{
		$result = [];

		foreach (self::DELIVERY_FIELDS_STRING_TYPE as $stringField)
		{
			if (!array_key_exists($stringField, $data))
			{
				continue;
			}

			$result[$stringField] = isset($data[$stringField])
				? (string)$data[$stringField]
				: null
			;
		}

		foreach (self::DELIVERY_FIELDS_INT_TYPE as $intField)
		{
			if (!array_key_exists($intField, $data))
			{
				continue;
			}

			$result[$intField] = isset($data[$intField])
				? (int)$data[$intField]
				: null
			;
		}

		return $result;
	}

	/**
	 * @param $params
	 * @throws RestException
	 * @throws AccessException
	 */
	private static function checkParamsBeforeDeliveryAdd($params): void
	{
		if (empty($params['REST_CODE']))
		{
			throw new RestException('Parameter REST_CODE is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (empty($params['NAME']))
		{
			throw new RestException('Parameter NAME is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (isset($params['CONFIG']))
		{
			if (!is_array($params['CONFIG']))
			{
				throw new RestException('Parameter CONFIG must be of array type', self::ERROR_CHECK_FAILURE);
			}

			self::checkConfig($params['CONFIG']);
		}

		if (empty($params['CURRENCY']))
		{
			throw new RestException('Parameter CURRENCY is not defined', self::ERROR_CHECK_FAILURE);
		}

		$handlerData = self::getHandlerData($params['REST_CODE']);
		if ($handlerData)
		{
			if ($params['APP_ID'] && !empty($handlerData['APP_ID']) && $handlerData['APP_ID'] !== $params['APP_ID'])
			{
				throw new AccessException();
			}
		}
		else
		{
			throw new RestException(
				'Handler "' . $params['REST_CODE'] . '" not exists', self::ERROR_HANDLER_NOT_FOUND
			);
		}
	}

	private static function prepareParamsBeforeDeliveryAdd(array $params): array
	{
		$params['CONFIG'] = self::prepareIncomingConfig($params['CONFIG'] ?? [], $params);

		if (isset($params['LOGOTIP']))
		{
			$params['LOGOTIP'] = self::saveFile($params['LOGOTIP']);
		}

		return $params;
	}

	/**
	 * @param $params
	 * @throws RestException
	 * @throws AccessException
	 */
	private static function checkParamsBeforeDeliveryUpdate($params): void
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

		if (array_key_exists('NAME', $params))
		{
			if (empty($params['NAME']))
			{
				throw new RestException('Parameter NAME should not be empty', self::ERROR_CHECK_FAILURE);
			}
		}

		if (!self::hasAccessToDelivery($data, $params['APP_ID']))
		{
			throw new AccessException();
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

		if (!self::hasAccessToDelivery($data, $params['APP_ID']))
		{
			throw new AccessException();
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

		$data = Delivery\Services\Manager::getById($params['ID']);
		if (!$data)
		{
			throw new RestException('Delivery not found', self::ERROR_DELIVERY_NOT_FOUND);
		}

		if (!self::hasAccessToDelivery($data, $params['APP_ID']))
		{
			throw new AccessException();
		}
	}

	private static function checkParamsBeforeDeliveryConfigUpdate($params): void
	{
		if (empty($params['ID']))
		{
			throw new RestException('Parameter ID is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (isset($params['CONFIG']))
		{
			if (!is_array($params['CONFIG']))
			{
				throw new RestException('Parameter CONFIG must be of array type', self::ERROR_CHECK_FAILURE);
			}

			self::checkConfig($params['CONFIG']);
		}

		$data = Delivery\Services\Manager::getById($params['ID']);
		if (!$data)
		{
			throw new RestException('Delivery not found', self::ERROR_DELIVERY_NOT_FOUND);
		}

		if (!self::hasAccessToDelivery($data, $params['APP_ID']))
		{
			throw new AccessException();
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
	 * @throws RestException
	 */
	public static function addDelivery($query, $n, \CRestServer $server)
	{
		self::checkDeliveryPermission();
		$params = self::prepareDeliveryParams($query, $server);
		self::checkParamsBeforeDeliveryAdd($params);

		$params = self::prepareParamsBeforeDeliveryAdd($params);

		$fields = [
			'NAME' => $params['NAME'],
			'DESCRIPTION' => $params['DESCRIPTION'] ?? '',
			'CLASS_NAME' => '\\' . \Sale\Handlers\Delivery\RestHandler::class,
			'CURRENCY' => $params['CURRENCY'],
			'SORT' => $params['SORT'] ?? 100,
			'ACTIVE' => $params['ACTIVE'] ?? 'Y',
			'CONFIG' => $params['CONFIG'],
			'LOGOTIP' => $params['LOGOTIP'] ?? null,
		];

		$result = Delivery\Services\Manager::add($fields);
		if ($result->isSuccess())
		{
			$parentDelivery = Delivery\Services\Manager::getList([
				'select' => self::ALLOWED_DELIVERY_FIELDS,
				'filter' => ['=ID' => (int)$result->getId()],
			])->fetch();

			$profiles = [];
			$profilesDeliveryList = Delivery\Services\Manager::getList([
				'select' => self::ALLOWED_DELIVERY_FIELDS,
				'filter' => ['=PARENT_ID' => (int)$result->getId()],
			]);
			while ($profileDelivery = $profilesDeliveryList->fetch())
			{
				$profiles[] = self::prepareOutcomingFields($profileDelivery);
			}

			return [
				'parent' => $parentDelivery ? self::prepareOutcomingFields($parentDelivery) : null,
				'profiles' => $profiles,
			];
		}

		$error = implode("\n", $result->getErrorMessages());
		throw new RestException($error, self::ERROR_DELIVERY_ADD);
	}

	/**
	 * @param $query
	 * @param $n
	 * @param \CRestServer $server
	 * @return bool
	 * @throws RestException
	 */
	public static function updateDelivery($query, $n, \CRestServer $server): bool
	{
		self::checkDeliveryPermission();
		$params = self::prepareDeliveryParams($query, $server);
		self::checkParamsBeforeDeliveryUpdate($params);

		$fields = [];

		if (array_key_exists('NAME', $params))
		{
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

		if (array_key_exists('CURRENCY', $params))
		{
			$fields['CURRENCY'] = (string)$params['CURRENCY'];
		}

		if (array_key_exists('LOGOTIP', $params))
		{
			$fields['LOGOTIP'] = self::saveFile($params['LOGOTIP']);
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
	 * @throws RestException
	 */
	public static function deleteDelivery($query, $n, \CRestServer $server): bool
	{
		self::checkDeliveryPermission();
		$params = self::prepareDeliveryParams($query, $server);
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
	 */
	public static function getDeliveryList($query, $n, \CRestServer $server): array
	{
		self::checkDeliveryPermission();
		$params = self::prepareIncomingParams($query);
		self::checkParamsBeforeDeliveryListGet($params);

		$select =
			isset($params['SELECT']) && is_array($params['SELECT'])
				? array_flip(self::prepareIncomingParams(array_flip($params['SELECT'])))
				: self::ALLOWED_DELIVERY_FIELDS
		;

		$filter = isset($params['FILTER']) && is_array($params['FILTER'])
			? $params['FILTER']
			: []
		;

		$order =
			isset($params['ORDER']) && is_array($params['ORDER'])
				? self::prepareIncomingParams($params['ORDER'])
				: []
		;

		$result = [];
		$deliveryListResult = Delivery\Services\Manager::getList([
			'select' => $select,
			'filter' => $filter,
			'order' => $order,
		]);
		while ($delivery = $deliveryListResult->fetch())
		{
			$result[] = self::prepareOutcomingFields($delivery);
		}

		return $result;
	}

	/**
	 * @param array $params
	 * @throws RestException
	 */
	private static function checkParamsBeforeDeliveryListGet(array $params)
	{
		$select = isset($params['SELECT']) && is_array($params['SELECT']) ? $params['SELECT'] : [];
		if ($select)
		{
			$select = array_flip(self::prepareIncomingParams(array_flip($select)));
			$diffSelect = array_diff($select, self::ALLOWED_DELIVERY_FIELDS);

			if ($diffSelect)
			{
				throw new RestException(
					implode(', ', $diffSelect) . ' not allowed for select',
					self::ERROR_CHECK_FAILURE
				);
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
			$diffFilter = array_diff($filterFields, self::ALLOWED_DELIVERY_FIELDS);
			if ($diffFilter)
			{
				throw new RestException(
					implode(', ', $diffFilter) . ' not allowed for filter',
					self::ERROR_CHECK_FAILURE
				);
			}
		}

		$order =
			isset($params['ORDER']) && is_array($params['ORDER'])
				? self::prepareIncomingParams($params['ORDER'])
				: []
		;
		if ($order)
		{
			$diffOrder = array_diff(array_keys($order), self::ALLOWED_DELIVERY_FIELDS);
			if ($diffOrder)
			{
				throw new RestException(
					implode(', ', $diffOrder) . ' not allowed for order',
					self::ERROR_CHECK_FAILURE
				);
			}
		}
	}

	/**
	 * @param $query
	 * @param $n
	 * @param \CRestServer $server
	 * @return array
	 * @throws RestException
	 */
	public static function getConfig($query, $n, \CRestServer $server): array
	{
		self::checkDeliveryPermission();
		$params = self::prepareDeliveryParams($query, $server);
		self::checkParamsBeforeDeliveryConfigGet($params);

		$result = [];

		$delivery = Delivery\Services\Manager::getById($params['ID']);
		if ($delivery)
		{
			if (is_array($delivery['CONFIG']))
			{
				$delivery['CONFIG'] = self::prepareOutcomingConfig($delivery['CONFIG']);
			}

			$result = is_array($delivery['CONFIG']) ? $delivery['CONFIG'] : [];
		}

		return $result;
	}

	/**
	 * @param $query
	 * @param $n
	 * @param \CRestServer $server
	 * @return bool
	 * @throws RestException
	 */
	public static function updateConfig($query, $n, \CRestServer $server): bool
	{
		self::checkDeliveryPermission();
		$params = self::prepareDeliveryParams($query, $server);
		self::checkParamsBeforeDeliveryConfigUpdate($params);

		$data = Delivery\Services\Manager::getById($params['ID']);
		$handlerCode = self::getRestCodeFromConfig($data['CONFIG']);
		$params['REST_CODE'] = $handlerCode;

		$result = Delivery\Services\Manager::update(
			$params['ID'],
			[
				'CONFIG' => self::prepareIncomingConfig($params['CONFIG'] ?? [], $params)
			]
		);
		if ($result->isSuccess())
		{
			return true;
		}

		$error = implode("\n", $result->getErrorMessages());
		throw new RestException($error, self::ERROR_DELIVERY_CONFIG_UPDATE);
	}

	private static function prepareIncomingConfig(array $config, array $params): array
	{
		$result = [
			'MAIN' => [
				'REST_CODE' => $params['REST_CODE']
			],
		];

		foreach ($config as $configItem)
		{
			$result['MAIN'][$configItem['CODE']] = $configItem['VALUE'];
		}

		return $result;
	}

	/**
	 * @param array $config
	 * @return array
	 */
	private static function prepareOutcomingConfig(array $config): array
	{
		if (isset($config['MAIN']['REST_CODE']))
		{
			unset($config['MAIN']['REST_CODE']);
		}

		$configItems = isset($config['MAIN']) && is_array($config['MAIN']) ? $config['MAIN'] : [];

		$result = [];
		foreach ($configItems as $configItemCode => $configItemValue)
		{
			$result[] = [
				'CODE' => $configItemCode,
				'VALUE' => $configItemValue,
			];
		}

		return $result;
	}

	private static function checkConfig(array $config): void
	{
		$requiredFields = [
			'CODE',
			'VALUE',
		];

		foreach ($config as $configItem)
		{
			foreach ($requiredFields as $requiredField)
			{
				if (
					!isset($configItem[$requiredField])
					|| $configItem[$requiredField] === ''
				)
				{
					throw new RestException(
						'Config item ' . $requiredField . ' is not specified',
						self::ERROR_CHECK_FAILURE
					);
				}
			}
		}
	}
}
