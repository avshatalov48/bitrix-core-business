<?php
use \Bitrix\Main\Localization\Loc as Loc;
use \Bitrix\Main\SystemException as SystemException;
use \Bitrix\Main\Loader as Loader;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class CSaleBusinessValueMail extends CBitrixComponent
{
	protected function getBusinessValueByOrderId($orderId, $providerCode, $fieldCode, $fieldGroup = null)
	{
		\Bitrix\Main\Loader::includeModule('sale');

		$order = \Bitrix\Sale\Order::loadByAccountNumber($orderId);

		if (empty($order))
		{
			$order = \Bitrix\Sale\Order::load($orderId);
		}

		/* @var $order \Bitrix\Sale\Order*/

		$providerList = \Bitrix\Sale\BusinessValue::getProviders();
		$provider = $providerList[$providerCode]['GET_INSTANCE_VALUE'];

		$personTypeId = null;
		$providerInstance = null;
		switch($providerCode)
		{
			case 'COMPANY':
				$paymentCollection = $order->getPaymentCollection();
				foreach($paymentCollection as $payment)
				{
					$providerInstance = $payment->getField('COMPANY_ID');
					if($providerInstance)
					{
						break;
					}
				}
				if($providerInstance)
				{
					$shipmentCollection = $order->getShipmentCollection();
					foreach($shipmentCollection as $shipment)
					{
						$providerInstance = $shipment->getField('COMPANY_ID');
						if($providerInstance)
						{
							break;
						}
					}
				}
				break;
			case 'ORDER':
				$providerInstance = $order;
				break;
			case 'USER':
				$providerInstance = $order->getUserId();
				break;
			case 'PAYMENT':
				$paymentCollection = $order->getPaymentCollection();
				foreach($paymentCollection as $payment)
				{
					$providerInstance = $payment;
					break;
				}
				break;
			case 'SHIPMENT':
				$shipmentCollection = $order->getShipmentCollection();
				foreach($shipmentCollection as $shipment)
				{
					$providerInstance = $shipment;
					break;
				}
				break;
			case 'PROPERTY':
				$providerInstance = $order;
				$personTypeId = $fieldGroup;
				break;
		}

		if($providerInstance)
		{
			return $provider($providerInstance, $fieldCode, $personTypeId);
		}
		else
		{
			return null;
		}
	}

	protected function getBusinessValueName($providerCode, $paramFieldCode)
	{
		$providerList = \Bitrix\Sale\BusinessValue::getProviders();

		if(!isset($providerList[$providerCode]['FIELDS']) || !is_array($providerList[$providerCode]['FIELDS']))
		{
			return null;
		}



		if(array_key_exists($paramFieldCode, $providerList[$providerCode]['FIELDS']))
		{
			return $providerList[$providerCode]['FIELDS'][$paramFieldCode]['NAME'];
		}
		else
		{
			foreach($providerList[$providerCode]['FIELDS'] as $fieldCode => $field)
			{
				$fieldCode = (isset($field['CODE']) && $field['CODE']) ? $field['CODE'] : $fieldCode;
				if($paramFieldCode == $fieldCode)
				{
					return $field['NAME'];
				}
			}
		}

		return null;
	}

	protected function getBusinessValueList()
	{
		$resultList = array();
		if (!empty($this->arParams['ORDER_ID']) && is_array($this->arParams['FIELD']))
		{
			foreach($this->arParams['FIELD'] as $field)
			{
				$value = $this->getBusinessValueByOrderId(
					$this->arParams['ORDER_ID'], $this->arParams['PROVIDER'],
					$field, $this->arParams['GROUP']
				);

				$resultList[] = array(
					'NAME' => $this->getBusinessValueName($this->arParams['PROVIDER'], $field),
					'CODE' => $field,
					'VALUE' => $value,
				);
			}
		}

		return $resultList;
	}

	/**
	 * @throws Exception
	 */
	protected function checkModules()
	{
		if(!Loader::includeModule("sale"))
		{
			throw new SystemException(Loc::getMessage("SALE_MODULE_NOT_INSTALLED"));
		}
	}

	public function executeComponent()
	{
		try
		{
			$this->checkModules();
			$this->arResult['ITEMS'] = $this->getBusinessValueList();
			$this->includeComponentTemplate();
		}
		catch (SystemException $e)
		{
			ShowError($e->getMessage());
		}
	}
}
