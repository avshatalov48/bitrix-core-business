<?php

namespace Bitrix\Sale\Exchange\OneC;


use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Config\Option;
use Bitrix\Sale\Exchange\EntityType;
use Bitrix\Sale\Exchange\ISettingsExport;

class ExportSettings extends SettingsBase
	implements ISettingsExport
{

	/**
	 * @return array|null
	 * @throws ArgumentNullException
	 */
	static protected function loadCurrentSettings()
	{
		if(self::$currentSettings === null)
		{
			self::$currentSettings['export']['SITE_ID'] = Option::get("sale", "1C_SALE_SITE_LIST", "");
			self::$currentSettings['export']['CURRENCY'] = Option::get("sale", "1C_REPLACE_CURRENCY", "");

			self::$currentSettings['payed'][EntityType::ORDER_NAME] = Option::get("sale", "1C_EXPORT_PAYED_ORDERS", "");
			self::$currentSettings['payed'][EntityType::SHIPMENT_NAME] = '';
			self::$currentSettings['payed'][EntityType::PAYMENT_CASH_NAME] = '';
			self::$currentSettings['payed'][EntityType::PAYMENT_CASH_LESS_NAME] = '';
			self::$currentSettings['payed'][EntityType::PAYMENT_CARD_TRANSACTION_NAME] = '';

			self::$currentSettings['payed'][EntityType::INVOICE_NAME] = Option::get("sale", "1C_EXPORT_PAYED_ORDERS", "");
			self::$currentSettings['payed'][EntityType::INVOICE_SHIPMENT_NAME] = '';
			self::$currentSettings['payed'][EntityType::INVOICE_PAYMENT_CASH_NAME] = '';
			self::$currentSettings['payed'][EntityType::INVOICE_PAYMENT_CASH_LESS_NAME] = '';
			self::$currentSettings['payed'][EntityType::INVOICE_PAYMENT_CARD_TRANSACTION_NAME] = '';


			self::$currentSettings['allow_delivery'][EntityType::ORDER_NAME] = Option::get("sale", "1C_EXPORT_ALLOW_DELIVERY_ORDERS", "");
			self::$currentSettings['allow_delivery'][EntityType::SHIPMENT_NAME] = '';
			self::$currentSettings['allow_delivery'][EntityType::PAYMENT_CASH_NAME] = '';
			self::$currentSettings['allow_delivery'][EntityType::PAYMENT_CASH_LESS_NAME] = '';
			self::$currentSettings['allow_delivery'][EntityType::PAYMENT_CARD_TRANSACTION_NAME] = '';

			self::$currentSettings['allow_delivery'][EntityType::INVOICE_NAME] = Option::get("sale", "1C_EXPORT_ALLOW_DELIVERY_ORDERS", "");
			self::$currentSettings['allow_delivery'][EntityType::INVOICE_SHIPMENT_NAME] = '';
			self::$currentSettings['allow_delivery'][EntityType::INVOICE_PAYMENT_CASH_NAME] = '';
			self::$currentSettings['allow_delivery'][EntityType::INVOICE_PAYMENT_CASH_LESS_NAME] = '';
			self::$currentSettings['allow_delivery'][EntityType::INVOICE_PAYMENT_CARD_TRANSACTION_NAME] = '';


			self::$currentSettings['accountNumberPrefix'][EntityType::ORDER_NAME] = Option::get("sale", "1C_SALE_ACCOUNT_NUMBER_SHOP_PREFIX", "");
			self::$currentSettings['accountNumberPrefix'][EntityType::SHIPMENT_NAME] = '';
			self::$currentSettings['accountNumberPrefix'][EntityType::PAYMENT_CASH_NAME] = '';
			self::$currentSettings['accountNumberPrefix'][EntityType::PAYMENT_CASH_LESS_NAME] = '';
			self::$currentSettings['accountNumberPrefix'][EntityType::PAYMENT_CARD_TRANSACTION_NAME] = '';

			self::$currentSettings['accountNumberPrefix'][EntityType::INVOICE_NAME] = Option::get("sale", "1C_SALE_ACCOUNT_NUMBER_SHOP_PREFIX", "");
			self::$currentSettings['accountNumberPrefix'][EntityType::INVOICE_SHIPMENT_NAME] = '';
			self::$currentSettings['accountNumberPrefix'][EntityType::INVOICE_PAYMENT_CASH_NAME] = '';
			self::$currentSettings['accountNumberPrefix'][EntityType::INVOICE_PAYMENT_CASH_LESS_NAME] = '';
			self::$currentSettings['accountNumberPrefix'][EntityType::INVOICE_PAYMENT_CARD_TRANSACTION_NAME] = '';


			self::$currentSettings['finalStatus'][EntityType::ORDER_NAME] = Option::get("sale", "1C_EXPORT_FINAL_ORDERS", "");
			self::$currentSettings['finalStatus'][EntityType::SHIPMENT_NAME] = '';
			self::$currentSettings['finalStatus'][EntityType::PAYMENT_CASH_NAME] = '';
			self::$currentSettings['finalStatus'][EntityType::PAYMENT_CASH_LESS_NAME] = '';
			self::$currentSettings['finalStatus'][EntityType::PAYMENT_CARD_TRANSACTION_NAME] = '';

			self::$currentSettings['finalStatus'][EntityType::INVOICE_NAME] = Option::get("sale", "1C_EXPORT_FINAL_ORDERS", "");
			self::$currentSettings['finalStatus'][EntityType::INVOICE_SHIPMENT_NAME] = '';
			self::$currentSettings['finalStatus'][EntityType::INVOICE_PAYMENT_CASH_NAME] = '';
			self::$currentSettings['finalStatus'][EntityType::INVOICE_PAYMENT_CASH_LESS_NAME] = '';
			self::$currentSettings['finalStatus'][EntityType::INVOICE_PAYMENT_CARD_TRANSACTION_NAME] = '';


			self::$currentSettings['groupPermission'][EntityType::ORDER_NAME] = Option::get("sale", "1C_SALE_GROUP_PERMISSIONS");
			self::$currentSettings['groupPermission'][EntityType::SHIPMENT_NAME] = '';
			self::$currentSettings['groupPermission'][EntityType::PAYMENT_CASH_NAME] = '';
			self::$currentSettings['groupPermission'][EntityType::PAYMENT_CASH_LESS_NAME] = '';
			self::$currentSettings['groupPermission'][EntityType::PAYMENT_CARD_TRANSACTION_NAME] = '';

			self::$currentSettings['groupPermission'][EntityType::INVOICE_NAME] = Option::get("sale", "1C_SALE_GROUP_PERMISSIONS");
			self::$currentSettings['groupPermission'][EntityType::INVOICE_SHIPMENT_NAME] = '';
			self::$currentSettings['groupPermission'][EntityType::INVOICE_PAYMENT_CASH_NAME] = '';
			self::$currentSettings['groupPermission'][EntityType::INVOICE_PAYMENT_CASH_LESS_NAME] = '';
			self::$currentSettings['groupPermission'][EntityType::INVOICE_PAYMENT_CARD_TRANSACTION_NAME] = '';


			if(!is_array(self::$currentSettings))
			{
				self::$currentSettings = array();
			}
		}
		return self::$currentSettings;
	}

	/**
	 * @return string
	 */
	public function getSiteId()
	{
		return $this->settings['export']['SITE_ID'] !== ""  ?  $this->settings['export']['SITE_ID']: '';
	}

	/**
	 * @return string
	 */
	public function getReplaceCurrency()
	{
		return $this->settings['export']['CURRENCY'];
	}

	/**
	 * @param $entityTypeId
	 * @return string
	 */
	public function groupPermissionFor($entityTypeId)
	{
		return $this->getValueFor($entityTypeId, 'groupPermission');
	}

	/**
	 * @param $entityTypeId
	 * @return string
	 */
	public function finalStatusFor($entityTypeId)
	{
		return $this->getValueFor($entityTypeId, 'finalStatus');
	}

	/**
	 * @param $entityTypeId
	 * @return string
	 */
	public function payedFor($entityTypeId)
	{
		return $this->getValueFor($entityTypeId, 'payed');
	}

	/**
	 * @param $entityTypeId
	 * @return string
	 */
	public function allowDeliveryFor($entityTypeId)
	{
		return $this->getValueFor($entityTypeId, 'allow_delivery');
	}

	/**
	 * @return ISettingsExport
	 */
	public static function getCurrent()
	{
		return new static(static::loadCurrentSettings());
	}
}