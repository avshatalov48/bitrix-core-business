<?php

namespace Bitrix\Sale\PaySystem;

use Bitrix\Main;
use Bitrix\Rest;
use Bitrix\Rest\AccessException;
use Bitrix\Rest\RestException;
use Bitrix\Sale\BusinessValue;
use Bitrix\Sale\Internals;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\Registry;
use Bitrix\Sale\Services\PaySystem\Restrictions;
use Bitrix\Crm\Invoice;

if (!Main\Loader::includeModule('rest'))
{
	return;
}

/**
 * Class RestService
 * @package Bitrix\Sale\PaySystem
 */
class RestService extends \IRestService
{
	const SCOPE = 'pay_system';

	const ERROR_CHECK_FAILURE = 'ERROR_CHECK_FAILURE';
	const ERROR_HANDLER_ALREADY_EXIST = 'ERROR_HANDLER_ALREADY_EXIST';
	const ERROR_HANDLER_NOT_FOUND = 'ERROR_HANDLER_NOT_FOUND';
	const ERROR_PERSON_TYPE_NOT_FOUND = 'ERROR_PERSON_TYPE_NOT_FOUND';
	const ERROR_PAY_SYSTEM_NOT_FOUND = 'ERROR_PAY_SYSTEM_NOT_FOUND';

	private const ERROR_HANDLER_ADD = 'ERROR_HANDLER_ADD';
	private const ERROR_HANDLER_UPDATE = 'ERROR_HANDLER_UPDATE';
	private const ERROR_HANDLER_DELETE = 'ERROR_HANDLER_DELETE';

	const ERROR_PAY_SYSTEM_ADD = 'ERROR_PAY_SYSTEM_ADD';
	const ERROR_PAY_SYSTEM_UPDATE = 'ERROR_PAY_SYSTEM_UPDATE';
	const ERROR_PAY_SYSTEM_DELETE = 'ERROR_PAY_SYSTEM_DELETE';

	const ERROR_INTERNAL_INVOICE_NOT_FOUND = 'ERROR_INTERNAL_INVOICE_NOT_FOUND';
	const ERROR_INTERNAL_ORDER_NOT_FOUND = 'ERROR_INTERNAL_ORDER_NOT_FOUND';
	const ERROR_PROCESS_REQUEST_RESULT = 'ERROR_PROCESS_REQUEST_RESULT';
	const ERROR_PAY_INVOICE_NOT_SUPPORTED = 'ERROR_INVOICE_NO_SUPPORTED';

	private const ALLOWED_PAYSYSTEM_FIELDS = [
		'ID', 'PERSON_TYPE_ID', 'NAME', 'PSA_NAME', 'SORT', 'DESCRIPTION', 'ACTION_FILE', 'RESULT_FILE',
		'NEW_WINDOW', 'TARIF', 'PS_MODE', 'HAVE_PAYMENT', 'HAVE_ACTION', 'HAVE_RESULT', 'HAVE_PREPAY',
		'HAVE_PRICE', 'HAVE_RESULT_RECEIVE', 'ENCODING', 'LOGOTIP', 'ACTIVE', 'ALLOW_EDIT_PAYMENT',
		'IS_CASH', 'AUTO_CHANGE_1C', 'CAN_PRINT_CHECK', 'ENTITY_REGISTRY_TYPE', 'XML_ID'
	];

	public static function onRestAppDelete(array $fields): void
	{
		if (!Main\Loader::includeModule('rest'))
		{
			return;
		}

		if (empty($fields['APP_ID']) || empty($fields['CLEAN']) || $fields['CLEAN'] !== true)
		{
			return;
		}

		$app = Rest\AppTable::getByClientId($fields['APP_ID']);
		if (!$app)
		{
			return;
		}

		$restHandlerResult = Internals\PaySystemRestHandlersTable::getList([
			'select' => ['ID', 'CODE'],
			'filter' => [
				'=APP_ID' => $app['CLIENT_ID'],
			],
		]);
		while ($restHandler = $restHandlerResult->fetch())
		{
			$paySystemResult = Manager::getList([
				'select' => ['ID'],
				'filter' => [
					'=ACTION_FILE' => $restHandler['CODE'],
				],
			]);
			while ($paySystem = $paySystemResult->fetch())
			{
				Manager::delete($paySystem['ID']);
			}

			Internals\PaySystemRestHandlersTable::delete($restHandler['ID']);
		}
	}

	/**
	 * @return array
	 */
	public static function onRestServiceBuildDescription()
	{
		return [
			static::SCOPE => [
				'sale.paysystem.handler.add' => [__CLASS__, 'addHandler'],
				'sale.paysystem.handler.update' => [__CLASS__, 'updateHandler'],
				'sale.paysystem.handler.delete' => [__CLASS__, 'deleteHandler'],
				'sale.paysystem.handler.list' => [__CLASS__, 'getHandlerList'],

				'sale.paysystem.add' => [__CLASS__, 'addPaySystem'],
				'sale.paysystem.update' => [__CLASS__, 'updatePaySystem'],
				'sale.paysystem.delete' => [__CLASS__, 'deletePaySystem'],
				'sale.paysystem.list' => [__CLASS__, 'getPaySystemList'],

				'sale.paysystem.settings.get' => [__CLASS__, 'getSettings'],
				'sale.paysystem.settings.update' => [__CLASS__, 'updateSettings'],

				'sale.paysystem.settings.invoice.get' => [__CLASS__, 'getSettingsByInvoice'],
				'sale.paysystem.settings.payment.get' => [__CLASS__, 'getSettingsByPayment'],

				'sale.paysystem.pay.invoice' => [__CLASS__, 'payInvoice'],
				'sale.paysystem.pay.payment' => [__CLASS__, 'payPayment'],
			]
		];
	}

	/**
	 * @param array $params
	 * @param $n
	 * @param \CRestServer $server
	 * @return array|int
	 * @throws RestException
	 */
	public static function addPaySystem(array $params, $n, \CRestServer $server)
	{
		static::checkPaySystemPermission();

		$params = self::preparePaySystemParams($params, $server);

		if (!isset($params['ENTITY_REGISTRY_TYPE']))
		{
			if (IsModuleInstalled('crm'))
			{
				$params['ENTITY_REGISTRY_TYPE'] = REGISTRY_TYPE_CRM_INVOICE;
			}
			else
			{
				$params['ENTITY_REGISTRY_TYPE'] = Registry::REGISTRY_TYPE_ORDER;
			}
		}

		static::checkParamsBeforePaySystemAdd($params);

		$fields = [
			'NAME' => $params['NAME'],
			'PSA_NAME' => $params['NAME'],
			'NEW_WINDOW' => $params['NEW_WINDOW'] ?: 'N',
			'ACTIVE' => $params['ACTIVE'] ?: 'N',
			'PERSON_TYPE_ID' => $params['PERSON_TYPE_ID'],
			'ACTION_FILE' => $params['BX_REST_HANDLER'],
			'HAVE_PREPAY' => 'N',
			'HAVE_RESULT' => 'N',
			'HAVE_ACTION' => 'N',
			'HAVE_PAYMENT' => 'N',
			'HAVE_RESULT_RECEIVE' => 'Y',
			'ENTITY_REGISTRY_TYPE' => $params['ENTITY_REGISTRY_TYPE'],
			'DESCRIPTION' => $params['DESCRIPTION'],
			'XML_ID' => $params['XML_ID'],
		];

		if (isset($params['LOGOTIP']))
		{
			$fields['LOGOTIP'] = self::saveFile($params['LOGOTIP']);
		}

		$result = Manager::add($fields);
		if ($result->isSuccess())
		{
			$id = $result->getId();
			Manager::update($id, array('PAY_SYSTEM_ID' => $id));

			foreach ($params['SETTINGS'] as $key => $value)
			{
				BusinessValue::setMapping(
					$key,
					Service::PAY_SYSTEM_PREFIX.$id,
					$params['PERSON_TYPE_ID'],
					[
						'PROVIDER_KEY' => $value['TYPE'],
						'PROVIDER_VALUE' => $value['VALUE']
					]
				);
			}

			if ($fields['PERSON_TYPE_ID'] > 0)
			{
				static::savePersonTypeId($id, $params['PERSON_TYPE_ID']);
			}

			static::logAnalytics(
				'addPaySystem' . $params['ENTITY_REGISTRY_TYPE'],
				$params['BX_REST_HANDLER'],
				$params['PERSON_TYPE_ID'],
				$server
			);

			return $id;
		}

		$error = implode("\n", $result->getErrorMessages());
		throw new RestException($error, self::ERROR_PAY_SYSTEM_ADD);
	}

	/**
	 * @param $params
	 * @throws RestException
	 * @throws AccessException
	 */
	protected static function checkParamsBeforePaySystemAdd($params)
	{
		if (empty($params['BX_REST_HANDLER']))
		{
			throw new RestException('Parameter BX_REST_HANDLER is not defined', self::ERROR_CHECK_FAILURE);
		}

		$handlerData = self::getHandlerData($params['BX_REST_HANDLER']);
		if (!$handlerData)
		{
			throw new RestException('Handler not found', self::ERROR_HANDLER_NOT_FOUND);
		}

		if ($params['APP_ID'] && !empty($handlerData['APP_ID']) && $handlerData['APP_ID'] !== $params['APP_ID'])
		{
			throw new AccessException();
		}

		$dbRes = Internals\PersonTypeTable::getList([
			'filter' => [
				'=ID' => $params['PERSON_TYPE_ID'],
				'=ENTITY_REGISTRY_TYPE' => $params['ENTITY_REGISTRY_TYPE'],
			]
		]);
		if (!$dbRes->fetch())
		{
			throw new RestException('Incorrect person type id!', self::ERROR_PERSON_TYPE_NOT_FOUND);
		}
	}

	/**
	 * @param $serviceId
	 * @param $personTypeId
	 */
	private static function savePersonTypeId($serviceId, $personTypeId)
	{
		$params = [
			'filter' => [
				"SERVICE_ID" => $serviceId,
				"SERVICE_TYPE" => Restrictions\Manager::SERVICE_TYPE_PAYMENT,
				"=CLASS_NAME" => '\\'.Restrictions\PersonType::class
			]
		];

		$dbRes = Internals\ServiceRestrictionTable::getList($params);
		if ($data = $dbRes->fetch())
		{
			$restrictionId = $data['ID'];
		}
		else
		{
			$restrictionId = 0;
		}

		$fields = array(
			"SERVICE_ID" => $serviceId,
			"SERVICE_TYPE" => Restrictions\Manager::SERVICE_TYPE_PAYMENT,
			"SORT" => 100,
			"PARAMS" => array('PERSON_TYPE_ID' => array($personTypeId))
		);

		Restrictions\PersonType::save($fields, $restrictionId);
	}

	/**
	 * @param array $params
	 * @param $n
	 * @param \CRestServer $server
	 * @return bool
	 */
	public static function updatePaySystem(array $params, $n, \CRestServer $server)
	{
		static::checkPaySystemPermission();

		$params = self::preparePaySystemParams($params, $server);

		static::checkParamsBeforePaySystemUpdate($params);

		$fields = array();
		if (isset($params['FIELDS']['NAME']))
		{
			$fields['NAME'] = $params['FIELDS']['NAME'];
		}

		if (isset($params['FIELDS']['NEW_WINDOW']))
		{
			$fields['NEW_WINDOW'] = $params['FIELDS']['NEW_WINDOW'];
		}

		if (isset($params['FIELDS']['ACTIVE']))
		{
			$fields['ACTIVE'] = $params['FIELDS']['ACTIVE'];
		}

		if (isset($params['FIELDS']['PERSON_TYPE_ID']))
		{
			$fields['PERSON_TYPE_ID'] = $params['FIELDS']['PERSON_TYPE_ID'];
		}

		if (isset($params['FIELDS']['BX_REST_HANDLER']))
		{
			$fields['ACTION_FILE'] = $params['FIELDS']['BX_REST_HANDLER'];
		}

		if (isset($params['FIELDS']['LOGOTIP']))
		{
			$fields['LOGOTIP'] = self::saveFile($params['FIELDS']['LOGOTIP']);
		}

		$result = Manager::update($params['ID'], $fields);

		if ($fields['PERSON_TYPE_ID'] > 0)
		{
			static::savePersonTypeId($params['ID'], $fields['PERSON_TYPE_ID']);
		}

		return $result->isSuccess();
	}

	/**
	 * @param $params
	 * @throws RestException
	 * @throws AccessException
	 */
	protected static function checkParamsBeforePaySystemUpdate($params)
	{
		$handlerList = Manager::getHandlerList();

		$handler = $params['FIELDS']['BX_REST_HANDLER'];
		if (!isset($handlerList['USER'][$handler]) && !isset($handlerList['SYSTEM'][$handler]))
		{
			throw new RestException('Handler not found', self::ERROR_HANDLER_NOT_FOUND);
		}

		$dbRes = Manager::getList([
			'filter' => [
				'ID' => $params['ID']
			]
		]);

		$data = $dbRes->fetch();
		if (!$data)
		{
			throw new RestException('Pay system not found', self::ERROR_PAY_SYSTEM_NOT_FOUND);
		}

		if (!self::hasAccessToPaySystem($data, $params['APP_ID']))
		{
			throw new AccessException();
		}

		$dbRes = Internals\PersonTypeTable::getList([
			'filter' => [
				'=ID' => $params['FIELDS']['PERSON_TYPE_ID'],
				'=ENTITY_REGISTRY_TYPE' => $data['ENTITY_REGISTRY_TYPE'],
			]
		]);
		if (!$dbRes->fetch())
		{
			throw new RestException('Incorrect person type id!', self::ERROR_PERSON_TYPE_NOT_FOUND);
		}
	}

	/**
	 * @param array $params
	 * @param $n
	 * @param \CRestServer $server
	 * @return bool
	 * @throws RestException
	 */
	public static function updateSettings(array $params, $n, \CRestServer $server)
	{
		static::checkPaySystemPermission();

		$params = self::preparePaySystemParams($params, $server);

		static::checkParamsBeforeSettingsUpdate($params);

		foreach ($params['SETTINGS'] as $field => $value)
		{
			$result = BusinessValue::setMapping(
				$field,
				Service::PAY_SYSTEM_PREFIX.$params['ID'],
				$params['PERSON_TYPE_ID'],
				[
					'PROVIDER_KEY' => $value['TYPE'],
					'PROVIDER_VALUE' => $value['VALUE']
				]
			);

			if (!$result->isSuccess())
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * @param $params
	 * @throws RestException
	 */
	protected static function checkParamsBeforeSettingsUpdate($params)
	{
		if (!isset($params['ID']))
		{
			throw new RestException('Parameter ID is not defined', self::ERROR_CHECK_FAILURE);
		}

		$item = Manager::getById($params['ID']);
		if (!$item)
		{
			throw new RestException('Pay system not found', static::ERROR_CHECK_FAILURE);
		}

		if (!isset($params['SETTINGS']) || empty($params['SETTINGS']))
		{
			throw new RestException('Parameter SETTINGS is not defined or empty', self::ERROR_HANDLER_NOT_FOUND);
		}

		if (!self::hasAccessToPaySystem($item, $params['APP_ID']))
		{
			throw new AccessException();
		}
	}

	/**
	 * @param array $params
	 * @param $n
	 * @param \CRestServer $server
	 * @return array
	 */
	public static function getSettings(array $params, $n, \CRestServer $server)
	{
		static::checkPaySystemPermission();

		$params = self::preparePaySystemParams($params, $server);

		static::checkParamsBeforeSettingsGet($params);

		$result = [];

		$consumers = BusinessValue::getConsumers();
		$codes = $consumers[Service::PAY_SYSTEM_PREFIX.$params['ID']]['CODES'];

		foreach ($codes as $field => $value)
		{
			$mapping = BusinessValue::getMapping(
				$field,
				Service::PAY_SYSTEM_PREFIX.$params['ID'],
				$params['PERSON_TYPE_ID']
			);

			$result[$field] = [
				'TYPE' => $mapping['PROVIDER_KEY'],
				'VALUE' => $mapping['PROVIDER_VALUE']
			];
		}

		return $result;
	}

	/**
	 * @param $params
	 * @throws RestException
	 * @throws AccessException
	 */
	protected static function checkParamsBeforeSettingsGet($params)
	{
		if (!isset($params['PERSON_TYPE_ID']))
		{
			throw new RestException('Parameter PERSON_TYPE_ID is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (!isset($params['ID']))
		{
			throw new RestException('Parameter ID is not defined', self::ERROR_CHECK_FAILURE);
		}

		$item = Manager::getById($params['ID']);
		if (!$item)
		{
			throw new RestException('Pay system not found', static::ERROR_CHECK_FAILURE);
		}

		if (!self::hasAccessToPaySystem($item, $params['APP_ID']))
		{
			throw new AccessException();
		}
	}

	/**
	 * @param array $params
	 * @return bool
	 */
	public static function deletePaySystem(array $params, $n, \CRestServer $server)
	{
		static::checkPaySystemPermission();

		$params = self::preparePaySystemParams($params, $server);

		static::checkParamsBeforePaySystemDelete($params);

		$result = Manager::delete($params['ID']);

		return $result->isSuccess();
	}

	/**
	 * @param $params
	 * @throws AccessException
	 * @throws RestException
	 */
	protected static function checkParamsBeforePaySystemDelete($params)
	{
		$data = Manager::getById($params['ID']);
		if (!$data)
		{
			throw new RestException('Pay system not found', self::ERROR_PAY_SYSTEM_NOT_FOUND);
		}

		if (!self::hasAccessToPaySystem($data, $params['APP_ID']))
		{
			throw new AccessException();
		}
	}

	/**
	 * @param array $params
	 * @param $n
	 * @param \CRestServer $server
	 * @return array|int
	 * @throws RestException
	 */
	public static function addHandler(array $params, $n, \CRestServer $server)
	{
		static::checkPaySystemPermission();

		$params = self::prepareHandlerParams($params, $server);

		self::checkParamsOnAddHandler($params);

		$data = [
			'NAME' => $params['NAME'],
			'CODE' => $params['CODE'],
			'SORT' => $params['SORT'] ?: 100,
			'SETTINGS' => $params['SETTINGS'],
			'APP_ID' => $params['APP_ID'],
		];

		$result = Internals\PaySystemRestHandlersTable::add($data);
		if ($result->isSuccess())
		{
			return $result->getId();
		}

		$error = implode("\n", $result->getErrorMessages());
		throw new RestException($error, self::ERROR_HANDLER_ADD);
	}

	/**
	 * @param array $params
	 * @throws RestException
	 */
	private static function checkParamsOnAddHandler(array $params)
	{
		if (!isset($params['NAME']))
		{
			throw new RestException('Parameter NAME is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (!isset($params['CODE']))
		{
			throw new RestException('Parameter CODE is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (!isset($params['SETTINGS']))
		{
			throw new RestException('Parameter SETTINGS is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (!isset($params['SETTINGS']['CODES']))
		{
			throw new RestException('Parameter SETTINGS[CODES] is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (
			empty($params['SETTINGS']['FORM_DATA'])
			&& empty($params['SETTINGS']['CHECKOUT_DATA'])
			&& empty($params['SETTINGS']['IFRAME_DATA'])
		)
		{
			throw new RestException(
				'Parameter SETTINGS[FORM_DATA] or SETTINGS[CHECKOUT_DATA] or SETTINGS[IFRAME_DATA] is not defined',
				self::ERROR_CHECK_FAILURE
			);
		}

		if (
			!empty($params['SETTINGS']['FORM_DATA'])
			&& empty($params['SETTINGS']['FORM_DATA']['ACTION_URI'])
		)
		{
			throw new RestException('Parameter SETTINGS[FORM_DATA][ACTION_URI] is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (
			!empty($params['SETTINGS']['CHECKOUT_DATA'])
			&& empty($params['SETTINGS']['CHECKOUT_DATA']['ACTION_URI'])
		)
		{
			throw new RestException('Parameter SETTINGS[IFRAME_DATA][ACTION_URI] is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (
			!empty($params['SETTINGS']['IFRAME_DATA'])
			&& empty($params['SETTINGS']['IFRAME_DATA']['ACTION_URI'])
		)
		{
			throw new RestException('Parameter SETTINGS[IFRAME_DATA][ACTION_URI] is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (
			isset($params['SETTINGS']['CLIENT_TYPE'])
			&& !ClientType::isValid((string)$params['SETTINGS']['CLIENT_TYPE'])
		)
		{
			throw new RestException('Parameter value SETTINGS[CLIENT_TYPE] is invalid', self::ERROR_CHECK_FAILURE);
		}

		$dbRes = Internals\PaySystemRestHandlersTable::getList([
			'filter' => [
				'=CODE' => $params['CODE']
			]
		]);
		if ($dbRes->fetch())
		{
			throw new RestException('Handler already exists!', self::ERROR_HANDLER_ALREADY_EXIST);
		}
	}

	/**
	 * @param array $params
	 * @param $n
	 * @param \CRestServer $server
	 * @return bool
	 * @throws RestException
	 */
	public static function updateHandler(array $params, $n, \CRestServer $server)
	{
		static::checkPaySystemPermission();

		$params = self::prepareHandlerParams($params, $server);

		self::checkParamsOnUpdateHandler($params);

		$result = Internals\PaySystemRestHandlersTable::update($params['ID'], $params['FIELDS']);
		if ($result->isSuccess())
		{
			return true;
		}

		$error = implode("\n", $result->getErrorMessages());
		throw new RestException($error, self::ERROR_HANDLER_UPDATE);
	}

	/**
	 * @param array $params
	 * @throws RestException
	 * @throws AccessException
	 */
	private static function checkParamsOnUpdateHandler(array $params)
	{
		if (!isset($params['FIELDS']))
		{
			throw new RestException('Parameter FIELDS is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (
			isset($params['SETTINGS']['CLIENT_TYPE'])
			&& !ClientType::isValid((string)$params['SETTINGS']['CLIENT_TYPE'])
		)
		{
			throw new RestException('Parameter value SETTINGS[CLIENT_TYPE] is invalid', self::ERROR_CHECK_FAILURE);
		}

		$paySystemRestHandlers = Internals\PaySystemRestHandlersTable::getList(array(
			'filter' => array(
				'ID' => $params['ID']
			)
		))->fetch();
		if (!$paySystemRestHandlers)
		{
			throw new RestException('Handler not found', self::ERROR_HANDLER_NOT_FOUND);
		}

		if ($params['APP_ID'] && !empty($paySystemRestHandlers['APP_ID']) && $paySystemRestHandlers['APP_ID'] !== $params['APP_ID'])
		{
			throw new AccessException();
		}
	}

	/**
	 * @param array $params
	 * @param $n
	 * @param \CRestServer $server
	 * @return bool
	 * @throws RestException
	 */
	public static function deleteHandler(array $params, $n, \CRestServer $server)
	{
		static::checkPaySystemPermission();

		$params = self::prepareHandlerParams($params, $server);

		self::checkParamsOnDeleteHandler($params);

		$result = Internals\PaySystemRestHandlersTable::delete($params['ID']);
		if ($result->isSuccess())
		{
			return true;
		}

		$error = implode("\n", $result->getErrorMessages());
		throw new RestException($error, self::ERROR_HANDLER_DELETE);
	}

	/**
	 * @param $params
	 * @throws RestException
	 */
	private static function checkParamsOnDeleteHandler($params): void
	{
		$dbRes = Internals\PaySystemRestHandlersTable::getList(array(
			'filter' => array(
				'ID' => $params['ID']
			)
		));
		$data = $dbRes->fetch();
		if (!$data)
		{
			throw new RestException('Handler not found', self::ERROR_HANDLER_NOT_FOUND);
		}

		if ($params['APP_ID'] && !empty($data['APP_ID']) && $data['APP_ID'] !== $params['APP_ID'])
		{
			throw new AccessException();
		}

		$dbRes = Manager::getList(array('filter' => array('ACTION_FILE' => $data['CODE'])));
		if ($dbRes->fetch())
		{
			throw new RestException('Pay system with handler '.ToUpper($data['CODE']).' exists!', self::ERROR_PAY_SYSTEM_DELETE);
		}
	}

	/**
	 * @param array $params
	 * @param $n
	 * @param \CRestServer $server
	 * @return array
	 */
	public static function getHandlerList(array $params, $n, \CRestServer $server)
	{
		static::checkPaySystemPermission();

		$result = array();
		$dbRes = Internals\PaySystemRestHandlersTable::getList([
			'select' => ['ID', 'NAME', 'CODE', 'SORT', 'SETTINGS'],
		]);
		while ($item = $dbRes->fetch())
		{
			$result[] = $item;
		}

		return $result;
	}

	/**
	 * @param array $params
	 * @param $n
	 * @param \CRestServer $server
	 * @return array
	 */
	public static function getPaySystemList(array $params, $n, \CRestServer $server)
	{
		static::checkPaySystemPermission();
		$params = self::prepareIncomingParams($params);
		self::checkParamsBeforePaySystemListGet($params);

		$select =
			isset($params['SELECT']) && is_array($params['SELECT'])
				? array_flip(self::prepareIncomingParams(array_flip($params['SELECT'])))
				: self::ALLOWED_PAYSYSTEM_FIELDS
		;

		$filter = [];
		$filterFromParams = isset($params['FILTER']) && is_array($params['FILTER']) ? $params['FILTER'] : [];
		if ($filterFromParams)
		{
			$incomingFieldsMap = self::getIncomingFieldsMap();
			foreach ($filterFromParams as $rawName => $value)
			{
				$filterField = \CSqlUtil::GetFilterOperation($rawName);
				$fieldName = $incomingFieldsMap[$filterField['FIELD']] ?? $filterField['FIELD'];
				$filter[$filterField['OPERATION'] . $fieldName] = $value;
			}
		}

		$order =
			isset($params['ORDER']) && is_array($params['ORDER'])
				? self::prepareIncomingParams($params['ORDER'])
				: []
		;

		$result = array();
		$dbRes = Manager::getList([
			'select' => $select,
			'filter' => $filter,
			'order' => $order,
		]);
		while ($item = $dbRes->fetch())
		{
			$result[] = self::prepareOutcomingFields($item);
		}

		return $result;
	}

	/**
	 * @param array $params
	 * @throws RestException
	 */
	private static function checkParamsBeforePaySystemListGet(array $params)
	{
		$select = isset($params['SELECT']) && is_array($params['SELECT']) ? $params['SELECT'] : [];
		if ($select)
		{
			$select = array_flip(self::prepareIncomingParams(array_flip($select)));
			$diffSelect = array_diff($select, self::ALLOWED_PAYSYSTEM_FIELDS);

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
			$diffFilter = array_diff($filterFields, self::ALLOWED_PAYSYSTEM_FIELDS);
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
			$diffOrder = array_diff(array_keys($order), self::ALLOWED_PAYSYSTEM_FIELDS);
			if ($diffOrder)
			{
				throw new RestException(implode(', ', $diffOrder) . ' not allowed for order');
			}
		}
	}

	/**
	 * @param array $params
	 * @param $n
	 * @param \CRestServer $server
	 * @return array
	 * @throws RestException
	 */
	public static function getSettingsByInvoice(array $params, $n, \CRestServer $server)
	{
		static::checkOrderPermission();

		$params = self::preparePaySystemParams($params, $server);

		self::checkParamsBeforeSettingsByInvoiceGet($params);

		if (isset($params['PAY_SYSTEM_ID']))
		{
			$service = Manager::getObjectById($params['PAY_SYSTEM_ID']);
		}
		else
		{
			$dbRes = Manager::getList(array('filter' => array('=ACTION_FILE' => $params['BX_REST_HANDLER'])));
			$item = $dbRes->fetch();
			if (!$item)
			{
				throw new RestException('Pay system with handler '.$params['BX_REST_HANDLER'].' not found', self::ERROR_PAY_SYSTEM_NOT_FOUND);
			}

			$service = new Service($item);
		}

		$invoice = Invoice\Invoice::load($params['INVOICE_ID']);
		if ($invoice)
		{
			$paymentCollection = $invoice->getPaymentCollection();
			if ($paymentCollection)
			{
				/** @var Payment $payment */
				foreach ($paymentCollection as $payment)
				{
					if (!$payment->isInner())
					{
						return $service->getParamsBusValue($payment);
					}
				}
			}
		}

		throw new RestException('Invoice #'.$params['INVOICE_ID'].' not found', self::ERROR_INTERNAL_INVOICE_NOT_FOUND);
	}

	/**
	 * @param array $params
	 * @param $n
	 * @param \CRestServer $server
	 * @return array
	 * @throws RestException
	 */
	public static function getSettingsByPayment(array $params, $n, \CRestServer $server)
	{
		static::checkOrderPermission();

		$params = self::preparePaySystemParams($params, $server);

		self::checkParamsBeforeSettingsByPaymentGet($params);

		list($orderId, $paymentId) = Manager::getIdsByPayment($params['PAYMENT_ID']);

		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);

		/** @var Order $orderClassName */
		$orderClassName = $registry->getOrderClassName();
		$order = $orderClassName::load($orderId);
		if ($order)
		{
			$paymentCollection = $order->getPaymentCollection();

			/** @var Payment $payment */
			$payment = $paymentCollection->getItemById($paymentId);

			$service = Manager::getObjectById($params['PAY_SYSTEM_ID']);

			return $service->getParamsBusValue($payment);
		}

		throw new RestException('Order #'.$orderId.' not found', self::ERROR_INTERNAL_ORDER_NOT_FOUND);
	}

	/**
	 * @param array $params
	 * @param $n
	 * @param \CRestServer $server
	 * @return bool
	 * @throws RestException
	 */
	public static function payInvoice(array $params, $n, \CRestServer $server)
	{
		if (!Main\Loader::includeModule('crm'))
		{
			throw new RestException('Pay invoice is not supported!', self::ERROR_PAY_INVOICE_NOT_SUPPORTED);
		}

		static::checkOrderPermission();

		$params = self::prepareIncomingParams($params);

		self::checkParamsForInvoice($params);

		$dbRes = Invoice\Payment::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'ORDER_ID' => $params['INVOICE_ID'],
				'!PAY_SYSTEM_ID' => Manager::getInnerPaySystemId(),
			)
		));

		$payment = $dbRes->fetch();
		if (!$payment)
		{
			throw new RestException('Invoice #'.$params['INVOICE_ID'].' not found', self::ERROR_INTERNAL_INVOICE_NOT_FOUND);
		}

		$params['PAYMENT_ID'] = $payment['ID'];

		$filter = [
			'=ENTITY_REGISTRY_TYPE' => REGISTRY_TYPE_CRM_INVOICE
		];

		if (isset($params['PAY_SYSTEM_ID']))
		{
			$filter['=ID'] = $params['PAY_SYSTEM_ID'];
		}
		else
		{
			$filter['=ACTION_FILE'] = $params['BX_REST_HANDLER'];
		}

		$dbRes = Manager::getList([
			'select' => ['ID'],
			'filter' => $filter
		]);
		$item = $dbRes->fetch();
		if (!$item)
		{
			throw new RestException('Pay system not found', static::ERROR_PROCESS_REQUEST_RESULT);
		}

		$params['PAY_SYSTEM_ID'] = $item['ID'];

		return self::payPaymentInternal($params, $server);
	}

	/**
	 * @param array $params
	 * @param $n
	 * @param \CRestServer $server
	 * @return bool
	 */
	public static function payPayment(array $params, $n, \CRestServer $server)
	{
		static::checkOrderPermission();

		$params = self::prepareIncomingParams($params);

		self::checkParamsForPayment($params);

		return self::payPaymentInternal($params, $server);
	}

	/**
	 * @param array $params
	 * @param \CRestServer $restServer
	 * @return bool
	 * @throws RestException
	 */
	private static function payPaymentInternal(array $params, \CRestServer $restServer)
	{
		$context = Main\Context::getCurrent();
		$server = $context->getServer();

		$request = new Main\HttpRequest($server, array(), $params, array(), array());

		$service = Manager::getObjectById($params['PAY_SYSTEM_ID']);

		$result = $service->processRequest($request);
		if (!$result->isSuccess())
		{
			$error = join("\n", $result->getErrorMessages());
			throw new RestException($error, static::ERROR_PROCESS_REQUEST_RESULT);
		}

		static::logAnalytics(
			'payPayment' . $service->getField('ENTITY_REGISTRY_TYPE'),
			$service->getField('ACTION_FILE'),
			$service->getField('PERSON_TYPE_ID'),
			$restServer
		);

		return true;
	}

	/**
	 * @param array $data
	 * @param int $case
	 * @return array
	 */
	private static function arrayChangeKeyCaseRecursive(array $data, $case = CASE_UPPER)
	{
		return array_map(static function ($item) use ($case) {
			if (is_array($item))
			{
				$item = self::arrayChangeKeyCaseRecursive($item, $case);
			}
			return $item;
		}, array_change_key_case($data, $case));
	}

	/**
	 * @param array $data
	 * @return array
	 */
	private static function prepareIncomingParams(array $data): array
	{
		return self::replaceIncomingKeys(self::arrayChangeKeyCaseRecursive($data));
	}

	/**
	 * @param array $data
	 * @param \CRestServer $server
	 * @return array
	 */
	private static function prepareHandlerParams(array $data, \CRestServer $server): array
	{
		$data = self::replaceIncomingKeys(array_change_key_case($data, CASE_UPPER));
		$data['APP_ID'] = $server->getClientId();

		return $data;
	}

	private static function preparePaySystemParams(array $data, \CRestServer $server): array
	{
		$data = self::prepareIncomingParams($data);
		$data['APP_ID'] = $server->getClientId();

		return $data;
	}

	/**
	 * @param array $params
	 * @throws RestException
	 */
	private static function checkParamsForInvoice(array $params)
	{
		if (!isset($params['BX_REST_HANDLER']) && !isset($params['PAY_SYSTEM_ID']))
		{
			throw new RestException('Empty field BX_REST_HANDLER and PAY_SYSTEM_ID', self::ERROR_CHECK_FAILURE);
		}

		if (isset($params['PAY_SYSTEM_ID']))
		{
			$data = Manager::getById($params['PAY_SYSTEM_ID']);
			if (!$data)
			{
				throw new RestException('Pay system with ID='.$params['PAY_SYSTEM_ID'].' not found', static::ERROR_CHECK_FAILURE);
			}
		}

		if (isset($params['BX_REST_HANDLER']))
		{
			$dbRes = Internals\PaySystemRestHandlersTable::getList(array(
				'filter' => array(
					'=CODE' => $params['BX_REST_HANDLER']
				)
			));
			if (!$dbRes->fetch())
			{
				throw new RestException('Incorrect rest handler code', static::ERROR_CHECK_FAILURE);
			}
		}

		if (empty($params['INVOICE_ID']))
		{
			throw new RestException('Empty field INVOICE_ID!', self::ERROR_CHECK_FAILURE);
		}
	}

	/**
	 * @param array $params
	 * @throws RestException
	 * @throws AccessException
	 */
	private static function checkParamsBeforeSettingsByInvoiceGet(array $params)
	{
		if (!isset($params['BX_REST_HANDLER']) && !isset($params['PAY_SYSTEM_ID']))
		{
			throw new RestException('Empty field BX_REST_HANDLER and PAY_SYSTEM_ID', self::ERROR_CHECK_FAILURE);
		}

		if (empty($params['INVOICE_ID']))
		{
			throw new RestException('Empty field INVOICE_ID', self::ERROR_CHECK_FAILURE);
		}

		if (isset($params['PAY_SYSTEM_ID']))
		{
			$data = Manager::getById($params['PAY_SYSTEM_ID']);
			if (!$data)
			{
				throw new RestException('Pay system with ID='.$params['PAY_SYSTEM_ID'].' not found', static::ERROR_CHECK_FAILURE);
			}

			if (!self::hasAccessToPaySystem($data, $params['APP_ID']))
			{
				throw new AccessException();
			}
		}

		if (isset($params['BX_REST_HANDLER']))
		{
			$dbRes = Internals\PaySystemRestHandlersTable::getList(array(
				'filter' => array(
					'=CODE' => $params['BX_REST_HANDLER']
				)
			));

			$handlerData = $dbRes->fetch();
			if (!$handlerData)
			{
				throw new RestException('Incorrect rest handler code', static::ERROR_CHECK_FAILURE);
			}

			if ($params['APP_ID'] && !empty($handlerData['APP_ID']) && $handlerData['APP_ID'] !== $params['APP_ID'])
			{
				throw new AccessException();
			}
		}
	}

	/**
	 * @param array $params
	 * @throws Main\ArgumentException
	 * @throws RestException
	 */
	private static function checkParamsForPayment(array $params)
	{
		if (empty($params['PAY_SYSTEM_ID']))
		{
			throw new RestException('Empty field PAY_SYSTEM_ID!', self::ERROR_CHECK_FAILURE);
		}

		$item = Manager::getById($params['PAY_SYSTEM_ID']);
		if (!$item)
		{
			throw new RestException('Pay system not found', static::ERROR_CHECK_FAILURE);
		}

		if (empty($params['PAYMENT_ID']))
		{
			throw new RestException('Empty field PAYMENT_ID', self::ERROR_CHECK_FAILURE);
		}

		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);

		/** @var Payment $paymentClassName */
		$paymentClassName = $registry->getPaymentClassName();
		$dbRes = $paymentClassName::getList([
			'select' => ['ID', 'PAY_SYSTEM_ID'],
			'filter' => [
				'=ID' => $params['PAYMENT_ID'],
				'=PAY_SYSTEM_ID' => $params['PAY_SYSTEM_ID']
			]
		]);

		if (!$dbRes->fetch())
		{
			throw new RestException(
				'Payment with ID='
				. $params['PAYMENT_ID']
				. ' and PAY_SYSTEM_ID='.$params['PAY_SYSTEM_ID']
				. ' not found', self::ERROR_CHECK_FAILURE
			);
		}
	}

	/**
	 * @param array $params
	 * @throws Main\ArgumentException
	 * @throws RestException
	 */
	private static function checkParamsBeforeSettingsByPaymentGet(array $params)
	{
		if (empty($params['PAY_SYSTEM_ID']))
		{
			throw new RestException('Empty field PAY_SYSTEM_ID!', self::ERROR_CHECK_FAILURE);
		}

		if (empty($params['PAYMENT_ID']))
		{
			throw new RestException('Empty field PAYMENT_ID', self::ERROR_CHECK_FAILURE);
		}

		$item = Manager::getById($params['PAY_SYSTEM_ID']);
		if (!$item)
		{
			throw new RestException('Pay system not found', static::ERROR_CHECK_FAILURE);
		}

		if (!self::hasAccessToPaySystem($item, $params['APP_ID']))
		{
			throw new AccessException();
		}

		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);

		/** @var Payment $paymentClassName */
		$paymentClassName = $registry->getPaymentClassName();
		$dbRes = $paymentClassName::getList([
			'select' => ['ID', 'PAY_SYSTEM_ID'],
			'filter' => [
				'=ID' => $params['PAYMENT_ID'],
				'=PAY_SYSTEM_ID' => $params['PAY_SYSTEM_ID']
			]
		]);

		if (!$dbRes->fetch())
		{
			throw new RestException(
				'Payment with ID='
				. $params['PAYMENT_ID']
				. ' and PAY_SYSTEM_ID='.$params['PAY_SYSTEM_ID']
				. ' not found', self::ERROR_CHECK_FAILURE
			);
		}
	}

	/**
	 * @throws AccessException
	 * @throws Main\LoaderException
	 */
	protected static function checkOrderPermission()
	{
		global $APPLICATION;

		if (IsModuleInstalled('intranet') && Main\Loader::includeModule('crm'))
		{
			$CCrmInvoice = new \CCrmInvoice();
			if ($CCrmInvoice->cPerms->HavePerm('INVOICE', BX_CRM_PERM_NONE, 'WRITE')
				&& $CCrmInvoice->cPerms->HavePerm('INVOICE', BX_CRM_PERM_NONE, 'ADD')
			)
			{
				throw new AccessException();
			}
		}
		else
		{
			$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

			if($saleModulePermissions == "D")
			{
				throw new AccessException();
			}
		}
	}

	/**
	 * @throws AccessException
	 * @throws Main\LoaderException
	 */
	protected static function checkPaySystemPermission()
	{
		\Bitrix\Sale\Helpers\Rest\AccessChecker::checkAccessPermission();
	}

	/**
	 * @param $fileContent
	 * @return false|int|string|null
	 */
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
	 * @return string[]
	 */
	private static function getIncomingFieldsMap(): array
	{
		return [
			'LOGOTYPE' => 'LOGOTIP',
			'TARIFF' => 'TARIF',
		];
	}

	/**
	 * @return string[]
	 */
	private static function getOutcomingFieldsMap(): array
	{
		return [
			'LOGOTIP' => 'LOGOTYPE',
			'TARIF' => 'TARIFF',
		];
	}

	/**
	 * @param array $data
	 * @return array
	 */
	private static function prepareOutcomingFields(array $data): array
	{
		return self::replaceOutcomingKeys($data);
	}

	/**
	 * @param array $data
	 * @return array
	 */
	private static function replaceIncomingKeys(array $data): array
	{
		return self::replaceKeys($data, self::getIncomingFieldsMap());
	}

	/**
	 * @param array $data
	 * @return array
	 */
	private static function replaceOutcomingKeys(array $data): array
	{
		return self::replaceKeys($data, self::getOutcomingFieldsMap());
	}

	/**
	 * @param array $data
	 * @param array $map
	 * @return array
	 */
	private static function replaceKeys(array $data, array $map): array
	{
		foreach ($map as $key => $newKey)
		{
			if (array_key_exists($key, $data))
			{
				$data[$newKey] = $data[$key];
				unset($data[$key]);
			}

			if (isset($data['FIELDS']) && array_key_exists($key, $data['FIELDS']))
			{
				$data['FIELDS'][$newKey] = $data['FIELDS'][$key];
				unset($data['FIELDS'][$key]);
			}
		}

		return $data;
	}

	private static function logAnalytics($action, $handler, $personType, \CRestServer $restServer) : bool
	{
		$code = '';
		$type = '';
		if ($restServer->getAuthType() === \Bitrix\Rest\OAuth\Auth::AUTH_TYPE)
		{
			$app = \Bitrix\Rest\AppTable::getByClientId($restServer->getClientId());
			if ($app['CODE'])
			{
				$code = $app['CODE'];
				$type = 'appCode';
			}
		}
		else
		{
			$code = $restServer->getPasswordId();
			$type = 'webHook';
		}

		if ($code !== '')
		{
			$tag = uniqid($code, true);
			AddEventToStatFile(
				'sale',
				$action,
				$tag,
				$code,
				$type
			);
			AddEventToStatFile(
				'sale',
				$action,
				$tag,
				$handler,
				'handler'
			);
			AddEventToStatFile(
				'sale',
				$action,
				$tag,
				$personType,
				'personType'
			);
		}

		return true;
	}

	private static function hasAccessToPaySystem(array $paySystemData, string $appId = null): bool
	{
		$handlerCode = $paySystemData['ACTION_FILE'];
		if (Manager::isRestHandler($handlerCode))
		{
			$handlerData = self::getHandlerData($handlerCode);
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

	private static function getHandlerData(string $code): ?array
	{
		static $result = [];

		if (!empty($result[$code]))
		{
			return $result[$code];
		}

		$handlerData = Internals\PaySystemRestHandlersTable::getList([
			'filter' => ['CODE' => $code],
			'limit' => 1,
		])->fetch();
		if (is_array($handlerData))
		{
			$result[$code] = $handlerData;
		}

		return $result[$code] ?? null;
	}
}