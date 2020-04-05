<?php

namespace Bitrix\Sale\PaySystem;

use Bitrix\Main\Context;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Rest\AccessException;
use Bitrix\Rest\RestException;
use Bitrix\Sale\BusinessValue;
use Bitrix\Sale\Internals;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\Services\PaySystem\Restrictions;

Loader::includeModule('rest');

Loc::loadMessages(__FILE__);

/**
 * Class RestService
 * @package Bitrix\Sale\PaySystem
 */
class RestService extends \IRestService
{
	const SCOPE = 'pay_system';

	const ERROR_VALIDATION_FAILURE = 'ERROR_VALIDATION_FAILURE';
	const ERROR_HANDLER_ALREADY_EXIST = 'ERROR_HANDLER_ALREADY_EXIST';
	const ERROR_HANDLER_NOT_FOUND = 'ERROR_HANDLER_NOT_FOUND';
	const ERROR_PAY_SYSTEM_ADD = 'ERROR_PAY_SYSTEM_ADD';
	const ERROR_PAY_SYSTEM_NOT_FOUND = 'ERROR_PAY_SYSTEM_NOT_FOUND';
	const ERROR_PAY_SYSTEM_DELETE = 'ERROR_PAY_SYSTEM_DELETE';
	const ERROR_INVOICE_NOT_FOUND = 'ERROR_INVOICE_NOT_FOUND';
	const ERROR_INTERNAL_INVOICE_NOT_FOUND = 'ERROR_INTERNAL_INVOICE_NOT_FOUND';
	const ERROR_PROCESS_REQUEST_RESULT = 'ERROR_PROCESS_REQUEST_RESULT';

	/**
	 * @return array
	 */
	public static function onRestServiceBuildDescription()
	{
		return array(
			static::SCOPE => array(
				'sale.paysystem.handler.add' => array(__CLASS__, 'addHandler'),
				'sale.paysystem.handler.update' => array(__CLASS__, 'updateHandler'),
				'sale.paysystem.handler.delete' => array(__CLASS__, 'deleteHandler'),
				'sale.paysystem.handler.list' => array(__CLASS__, 'getHandlerList'),

				'sale.paysystem.add' => array(__CLASS__, 'addPaySystem'),
				'sale.paysystem.update' => array(__CLASS__, 'updatePaySystem'),
				'sale.paysystem.delete' => array(__CLASS__, 'deletePaySystem'),
				'sale.paysystem.list' => array(__CLASS__, 'getPaySystemList'),
				'sale.paysystem.settings.invoice.get' => array(__CLASS__, 'getSettingsByInvoice'),
				'sale.paysystem.pay.invoice' => array(__CLASS__, 'payInvoice'),
			)
		);
	}

	/**
	 * @param array $params
	 * @return int
	 * @throws RestException
	 */
	public static function addPaySystem(array $params)
	{
		static::checkPaySystemPermission();

		$params = static::prepareParams($params);

		$handlerList = Manager::getHandlerList();
		if (!isset($handlerList['USER'][$params['BX_REST_HANDLER']]) && !isset($handlerList['SYSTEM'][$params['BX_REST_HANDLER']]))
		{
			throw new RestException('Handler not found!', self::ERROR_HANDLER_NOT_FOUND);
		}

		$fields = array(
			'NAME' => $params['NAME'],
			'ACTIVE' => $params['ACTIVE'] ?: 'N',
			'PERSON_TYPE_ID' => $params['PERSON_TYPE_ID'],
			'ACTION_FILE' => $params['BX_REST_HANDLER'],
			'HAVE_PREPAY' => 'N',
			'HAVE_RESULT' => 'N',
			'HAVE_ACTION' => 'N',
			'HAVE_PAYMENT' => 'N',
			'HAVE_RESULT_RECEIVE' => 'Y'
		);

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
					array(
						'PROVIDER_KEY' => $value['TYPE'],
						'PROVIDER_VALUE' => $value['VALUE']
					)
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
	 * @param $serviceId
	 * @param $personTypeId
	 */
	private static function savePersonTypeId($serviceId, $personTypeId)
	{
		$params = array(
			'filter' => array(
				"SERVICE_ID" => $serviceId,
				"SERVICE_TYPE" => Restrictions\Manager::SERVICE_TYPE_PAYMENT,
				"=CLASS_NAME" => '\Bitrix\Sale\Services\PaySystem\Restrictions\PersonType'
			)
		);

		$dbRes = Internals\ServiceRestrictionTable::getList($params);
		if ($data = $dbRes->fetch())
			$restrictionId = $data['ID'];
		else
			$restrictionId = 0;

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
	 * @throws RestException
	 */
	public static function updatePaySystem(array $params)
	{
		static::checkPaySystemPermission();

		$params = static::prepareParams($params);

		$fields = array();
		if (isset($params['FIELDS']['NAME']))
			$fields['NAME'] = $params['FIELDS']['NAME'];

		if (isset($params['FIELDS']['ACTIVE']))
			$fields['ACTIVE'] = $params['FIELDS']['ACTIVE'];

		if (isset($params['FIELDS']['PERSON_TYPE_ID']))
			$fields['PERSON_TYPE_ID'] = $params['FIELDS']['PERSON_TYPE_ID'];

		if (isset($params['FIELDS']['BX_REST_HANDLER']))
		{
			$fields['ACTION_FILE'] = $params['FIELDS']['BX_REST_HANDLER'];

			$handlerList = Manager::getHandlerList();
			if (!isset($handlerList['USER'][$fields['ACTION_FILE']]) && !isset($handlerList['SYSTEM'][$fields['ACTION_FILE']]))
			{
				throw new RestException('Handler not found!', self::ERROR_HANDLER_NOT_FOUND);
			}
		}

		$dbRes = Manager::getList(array(
			'filter' => array(
				'ID' => $params['ID']
			)
		));
		if (!$dbRes->fetch())
		{
			throw new RestException('Pay system not found!', self::ERROR_PAY_SYSTEM_NOT_FOUND);
		}

		$result = Manager::update($params['ID'], $fields);

		if ($fields['PERSON_TYPE_ID'] > 0)
		{
			static::savePersonTypeId($params['ID'], $fields['PERSON_TYPE_ID']);
		}

		return $result->isSuccess();
	}

	/**
	 * @param array $params
	 * @return bool
	 * @throws RestException
	 */
	public static function deletePaySystem(array $params)
	{
		static::checkPaySystemPermission();

		$params = static::prepareParams($params);

		$data = Manager::getById($params['ID']);
		if (!$data)
		{
			throw new RestException('Pay system not found!', self::ERROR_PAY_SYSTEM_NOT_FOUND);
		}

		$result = Manager::delete($data['ID']);
		return $result->isSuccess();
	}

	/**
	 * @param array $params
	 * @return bool|int
	 * @throws RestException
	 */
	public static function addHandler(array $params)
	{
		static::checkPaySystemPermission();

		$params = static::prepareParams($params);

		$dbRes = Internals\PaySystemRestHandlersTable::getList(array(
			'filter' => array(
				'=CODE' => $params['CODE']
			)
		));
		if ($data = $dbRes->fetch())
		{
			throw new RestException('Handler already exists!', self::ERROR_HANDLER_ALREADY_EXIST);
		}

		$result = Internals\PaySystemRestHandlersTable::add($params);
		if ($result->isSuccess())
			return $result->getId();

		return false;
	}

	/**
	 * @param array $params
	 * @return bool
	 * @throws RestException
	 */
	public static function updateHandler(array $params)
	{
		static::checkPaySystemPermission();

		$params = static::prepareParams($params);

		$dbRes = Internals\PaySystemRestHandlersTable::getList(array(
			'filter' => array(
				'ID' => $params['ID']
			)
		));
		if (!$dbRes->fetch())
		{
			throw new RestException('Handler not found!', self::ERROR_HANDLER_NOT_FOUND);
		}

		$result = Internals\PaySystemRestHandlersTable::update($params['ID'], $params['FIELDS']);
		return $result->isSuccess();
	}

	/**
	 * @param array $params
	 * @return bool
	 * @throws RestException
	 */
	public static function deleteHandler(array $params)
	{
		static::checkPaySystemPermission();

		$params = static::prepareParams($params);

		$dbRes = Internals\PaySystemRestHandlersTable::getList(array(
			'filter' => array(
				'ID' => $params['ID']
			)
		));
		$data = $dbRes->fetch();
		if (!$data)
		{
			throw new RestException('Handler not found!', self::ERROR_HANDLER_NOT_FOUND);
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
	 * @return array
	 * @throws AccessException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getPaySystemList()
	{
		static::checkPaySystemPermission();

		$result = array();
		$dbRes = Manager::getList();
		while ($item = $dbRes->fetch())
		{
			$result[] = $item;
		}

		return $result;
	}

	/**
	 * @param array $params
	 * @return array
	 * @throws RestException
	 */
	public static function getSettingsByInvoice(array $params)
	{
		static::checkOrderPermission();

		$params = static::prepareParams($params);
		static::validateParams($params);

		$handlerCode = $params['BX_REST_HANDLER'];
		$invoiceId = $params['INVOICE_ID'];

		if (empty($invoiceId))
		{
			throw new RestException('Invoice #'.$invoiceId.' not found!', self::ERROR_INVOICE_NOT_FOUND);
		}

		$dbRes = Manager::getList(array('filter' => array('ACTION_FILE' => $handlerCode)));
		$item = $dbRes->fetch();
		if (!$item)
		{
			throw new RestException('Pay system with handler '.$handlerCode.' not found!', self::ERROR_PAY_SYSTEM_NOT_FOUND);
		}

		$order = Order::load($invoiceId);
		if ($order)
		{
			$paymentCollection = $order->getPaymentCollection();
			if ($paymentCollection)
			{
				/** @var Payment $payment */
				foreach ($paymentCollection as $payment)
				{
					if (!$payment->isInner())
					{
						$service = new Service($item);
						return $service->getParamsBusValue($payment);
					}
				}
			}
		}

		throw new RestException('Payment of invoice #'.$invoiceId.' not found!', self::ERROR_INTERNAL_INVOICE_NOT_FOUND);
	}

	/**
	 * @param array $params
	 * @return bool
	 * @throws RestException
	 */
	public static function payInvoice(array $params)
	{
		static::checkOrderPermission();

		$params = static::prepareParams($params);

		if (!isset($params['INVOICE_ID']))
		{
			throw new RestException('Invoice #'.$params['INVOICE_ID'].' not found!', self::ERROR_INVOICE_NOT_FOUND);
		}

		$dbRes = Payment::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'ORDER_ID' => $params['INVOICE_ID'],
				'!PAY_SYSTEM_ID' => Manager::getInnerPaySystemId(),
			)
		));

		$payment = $dbRes->fetch();
		if (!$payment)
		{
			throw new RestException('Incorrect invoice #'.$params['INVOICE_ID'], self::ERROR_INTERNAL_INVOICE_NOT_FOUND);
		}

		$params['PAYMENT_ID'] = $payment['ID'];
		return static::payPayment($params);
	}

	/**
	 * @param array $params
	 * @return bool
	 * @throws RestException
	 */
	private static function payPayment(array $params)
	{
		$context = Context::getCurrent();
		$server = $context->getServer();
		$request = new HttpRequest($server, array(), $params, array(), array());

		$dbRes = Internals\PaySystemRestHandlersTable::getList(array(
			'filter' => array(
				'=CODE' => $params['BX_REST_HANDLER']
			)
		));
		if (!$dbRes->fetch())
		{
			throw new RestException('Incorrect rest handler code', static::ERROR_HANDLER_NOT_FOUND);
		}

		$dbRes = Manager::getList(array(
			'filter' => array(
				'=ACTION_FILE' => $params['BX_REST_HANDLER']
			)
		));
		$item = $dbRes->fetch();
		if ($item !== false)
		{
			$service = new Service($item);
			$result = $service->processRequest($request);
			if (!$result->isSuccess())
			{
				$error = join("\n", $result->getErrorMessages());
				throw new RestException($error, static::ERROR_PROCESS_REQUEST_RESULT);
			}

			return true;
		}

		throw new RestException('Pay system not found', static::ERROR_PROCESS_REQUEST_RESULT);
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
	 * @throws RestException
	 */
	private static function validateParams(array $params)
	{
		if (empty($params['BX_REST_HANDLER']))
		{
			throw new RestException('Empty field BX_REST_HANDLER!', self::ERROR_VALIDATION_FAILURE);
		}

		if (empty($params['INVOICE_ID']))
		{
			throw new RestException('Empty field INVOICE_ID!', self::ERROR_VALIDATION_FAILURE);
		}
	}

	/**
	 * @throws AccessException
	 * @throws \Bitrix\Main\LoaderException
	 */
	protected static function checkOrderPermission()
	{
		global $APPLICATION;

		if (IsModuleInstalled('intranet') && Loader::includeModule('crm'))
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
	 */
	protected static function checkPaySystemPermission()
	{
		global $APPLICATION, $USER;

		if (IsModuleInstalled('intranet') && Loader::includeModule('crm'))
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
}