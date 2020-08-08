<?php

namespace Bitrix\Sale\PaySystem;

use Bitrix\Main;
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
	const ERROR_PAY_SYSTEM_ADD = 'ERROR_PAY_SYSTEM_ADD';
	const ERROR_PAY_SYSTEM_NOT_FOUND = 'ERROR_PAY_SYSTEM_NOT_FOUND';
	const ERROR_PAY_SYSTEM_DELETE = 'ERROR_PAY_SYSTEM_DELETE';
	const ERROR_INTERNAL_INVOICE_NOT_FOUND = 'ERROR_INTERNAL_INVOICE_NOT_FOUND';
	const ERROR_INTERNAL_ORDER_NOT_FOUND = 'ERROR_INTERNAL_ORDER_NOT_FOUND';
	const ERROR_PROCESS_REQUEST_RESULT = 'ERROR_PROCESS_REQUEST_RESULT';
	const ERROR_PAY_INVOICE_NOT_SUPPORTED = 'ERROR_INVOICE_NO_SUPPORTED';

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
	 * @return array|int
	 * @throws AccessException
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\IO\FileNotFoundException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws RestException
	 */
	public static function addPaySystem(array $params)
	{
		static::checkPaySystemPermission();

		$params = self::prepareParams($params);

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
			'ACTIVE' => $params['ACTIVE'] ?: 'N',
			'PERSON_TYPE_ID' => $params['PERSON_TYPE_ID'],
			'ACTION_FILE' => $params['BX_REST_HANDLER'],
			'HAVE_PREPAY' => 'N',
			'HAVE_RESULT' => 'N',
			'HAVE_ACTION' => 'N',
			'HAVE_PAYMENT' => 'N',
			'HAVE_RESULT_RECEIVE' => 'Y',
			'ENTITY_REGISTRY_TYPE' => $params['ENTITY_REGISTRY_TYPE'],
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

			return $id;
		}

		$error = implode("\n", $result->getErrorMessages());
		throw new RestException($error, self::ERROR_PAY_SYSTEM_ADD);
	}

	/**
	 * @param $params
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\IO\FileNotFoundException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws RestException
	 */
	protected static function checkParamsBeforePaySystemAdd($params)
	{
		$handlerList = Manager::getHandlerList();
		if (!isset($handlerList['USER'][$params['BX_REST_HANDLER']]) && !isset($handlerList['SYSTEM'][$params['BX_REST_HANDLER']]))
		{
			throw new RestException('Handler not found', self::ERROR_HANDLER_NOT_FOUND);
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
	 * @throws \Exception
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
	 * @return bool
	 * @throws AccessException
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\IO\FileNotFoundException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws RestException
	 */
	public static function updatePaySystem(array $params)
	{
		static::checkPaySystemPermission();

		$params = self::prepareParams($params);

		static::checkParamsBeforePaySystemUpdate($params);

		$fields = array();
		if (isset($params['FIELDS']['NAME']))
		{
			$fields['NAME'] = $params['FIELDS']['NAME'];
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
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\IO\FileNotFoundException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws RestException
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
	 * @param $params
	 * @return bool
	 * @throws AccessException
	 * @throws Main\LoaderException
	 * @throws RestException
	 */
	public static function updateSettings($params)
	{
		static::checkPaySystemPermission();

		$params = self::prepareParams($params);

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
	}

	/**
	 * @param $params
	 * @return array
	 * @throws AccessException
	 * @throws Main\LoaderException
	 * @throws Main\SystemException
	 * @throws RestException
	 */
	public static function getSettings($params)
	{
		static::checkPaySystemPermission();

		$params = self::prepareParams($params);

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
	}

	/**
	 * @param array $params
	 * @return bool
	 * @throws AccessException
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws RestException
	 */
	public static function deletePaySystem(array $params)
	{
		static::checkPaySystemPermission();

		$params = self::prepareParams($params);

		static::checkParamsBeforePaySystemDelete($params);

		$result = Manager::delete($params['ID']);

		return $result->isSuccess();
	}

	/**
	 * @param $params
	 * @throws RestException
	 */
	protected static function checkParamsBeforePaySystemDelete($params)
	{
		$data = Manager::getById($params['ID']);
		if (!$data)
		{
			throw new RestException('Pay system not found', self::ERROR_PAY_SYSTEM_NOT_FOUND);
		}
	}

	/**
	 * @param array $params
	 * @return array|bool|int
	 * @throws AccessException
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws RestException
	 */
	public static function addHandler(array $params)
	{
		static::checkPaySystemPermission();

		$params = self::prepareParams($params);

		self::checkParamsOnAddHandler($params);

		$result = Internals\PaySystemRestHandlersTable::add([
			'NAME' => $params['NAME'],
			'CODE' => $params['CODE'],
			'SORT' => $params['SORT'] ?: 100,
			'SETTINGS' => $params['SETTINGS'],
		]);
		if ($result->isSuccess())
		{
			return $result->getId();
		}

		return false;
	}

	/**
	 * @param array $params
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
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


		$dbRes = Internals\PaySystemRestHandlersTable::getList([
			'filter' => [
				'=CODE' => $params['CODE']
			]
		]);
		if ($data = $dbRes->fetch())
		{
			throw new RestException('Handler already exists!', self::ERROR_HANDLER_ALREADY_EXIST);
		}
	}

	/**
	 * @param array $params
	 * @return bool
	 * @throws AccessException
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws RestException
	 */
	public static function updateHandler(array $params)
	{
		static::checkPaySystemPermission();

		$params = self::prepareParams($params);

		self::checkParamsOnUpdateHandler($params);

		$result = Internals\PaySystemRestHandlersTable::update($params['ID'], $params['FIELDS']);
		return $result->isSuccess();
	}

	/**
	 * @param array $params
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws RestException
	 */
	private static function checkParamsOnUpdateHandler(array $params)
	{
		$dbRes = Internals\PaySystemRestHandlersTable::getList(array(
			'filter' => array(
				'ID' => $params['ID']
			)
		));
		if (!$dbRes->fetch())
		{
			throw new RestException('Handler not found', self::ERROR_HANDLER_NOT_FOUND);
		}

		if (!isset($params['FIELDS']))
		{
			throw new RestException('Parameter FIELDS is not defined', self::ERROR_CHECK_FAILURE);
		}
	}

	/**
	 * @param array $params
	 * @return bool
	 * @throws AccessException
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws RestException
	 */
	public static function deleteHandler(array $params)
	{
		static::checkPaySystemPermission();

		$params = self::prepareParams($params);

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

		$dbRes = Manager::getList(array('filter' => array('ACTION_FILE' => $data['CODE'])));
		if ($dbRes->fetch())
		{
			throw new RestException('Pay system with handler '.ToUpper($data['CODE']).' exists!', self::ERROR_PAY_SYSTEM_DELETE);
		}

		$result = Internals\PaySystemRestHandlersTable::delete($params['ID']);
		return $result->isSuccess();
	}

	/**
	 * @return array
	 * @throws AccessException
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getHandlerList()
	{
		static::checkPaySystemPermission();

		$result = array();
		$dbRes = Internals\PaySystemRestHandlersTable::getList();
		while ($item = $dbRes->fetch())
			$result[] = $item;

		return $result;
	}

	/**
	 * @param array $params
	 * @return array
	 * @throws AccessException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getPaySystemList(array $params=[])
	{
		static::checkPaySystemPermission();

		$select = isset($params['select']) && is_array($params['select']) ? $params['select']:['*'];
		$filter = isset($params['filter']) && is_array($params['filter']) ? self::prepareParams($params['filter']):[];
		$order = isset($params['order']) && is_array($params['order']) ? self::prepareParams($params['order']):[];

		$result = array();
		$dbRes = Manager::getList(
			[
				'select'=>$select,
				'filter'=>$filter,
				'order'=>$order
			]
		);
		while ($item = $dbRes->fetch())
		{
			unset($item['PAY_SYSTEM_ID']);
			unset($item['PARAMS']);
			$result[] = $item;
		}

		return $result;
	}

	/**
	 * @param array $params
	 * @return array
	 * @throws AccessException
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws RestException
	 */
	public static function getSettingsByInvoice(array $params)
	{
		static::checkOrderPermission();

		$params = self::prepareParams($params);

		self::checkParamsForInvoice($params);

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
	 * @return array
	 * @throws AccessException
	 * @throws RestException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getSettingsByPayment(array $params)
	{
		static::checkOrderPermission();

		$params = self::prepareParams($params);

		self::checkParamsForPayment($params);

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
	 * @return bool
	 * @throws AccessException
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws RestException
	 */
	public static function payInvoice(array $params)
	{
		if (!Main\Loader::includeModule('crm'))
		{
			throw new RestException('Pay invoice is not supported!', self::ERROR_PAY_INVOICE_NOT_SUPPORTED);
		}

		static::checkOrderPermission();

		$params = self::prepareParams($params);

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

		return self::payPaymentInternal($params);
	}

	/**
	 * @param array $params
	 * @return bool
	 * @throws AccessException
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws RestException
	 */
	public static function payPayment(array $params)
	{
		static::checkOrderPermission();

		$params = self::prepareParams($params);

		self::checkParamsForPayment($params);

		return self::payPaymentInternal($params);
	}

	/**
	 * @param array $params
	 * @return bool
	 * @throws RestException
	 */
	private static function payPaymentInternal(array $params)
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

		return true;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	private static function prepareParams(array $data)
	{
		return array_change_key_case($data, CASE_UPPER);
	}

	/**
	 * @param array $params
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
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
				'Payment with ID='.$params['PAYMENT_ID'].
				' and PAY_SYSTEM_ID='.$params['PAY_SYSTEM_ID'].
				' not found', self::ERROR_CHECK_FAILURE
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
		global $APPLICATION, $USER;

		if (IsModuleInstalled('intranet') && Main\Loader::includeModule('crm'))
		{
			$CrmPerms = new \CCrmPerms($USER->GetID());
			if (!$CrmPerms->HavePerm('CONFIG', BX_CRM_PERM_CONFIG, 'WRITE'))
			{
				throw new AccessException();
			}
		}
		else
		{
			$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
			if ($saleModulePermissions < "W")
			{
				throw new AccessException();
			}
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
}