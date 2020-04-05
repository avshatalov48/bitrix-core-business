<?php
namespace Bitrix\Sale\Exchange\OneC;


use Bitrix\Main;
use Bitrix\Main\Config\Option;
use Bitrix\Sale\Delivery\Services\EmptyDeliveryService;
use Bitrix\Sale\Exchange;
use Bitrix\Sale\PaySystem\Manager;

class ImportSettings  extends SettingsBase
	implements Exchange\ISettingsImport
{
	/**
	 * @return array|null
	 * @throws Main\ArgumentNullException
	 */
	static protected function loadCurrentSettings()
	{
		if(self::$currentSettings === null)
		{
			self::$currentSettings['import']['CURRENCY'] = \CSaleLang::GetLangCurrency(Option::get("sale", "1C_SITE_NEW_ORDERS"));
			self::$currentSettings['import']['SITE_ID'] = Option::get("sale", "1C_SITE_NEW_ORDERS");

			self::$currentSettings['finalStatusId'][Exchange\EntityType::ORDER_NAME] = "F";
			self::$currentSettings['finalStatusOnDelivery'][Exchange\EntityType::ORDER_NAME] = Option::get("sale", "1C_FINAL_STATUS_ON_DELIVERY", "");

			self::$currentSettings['changeStatusFor'][Exchange\EntityType::ORDER_NAME] = Option::get("sale", "1C_CHANGE_STATUS_FROM_1C", "Y");
			self::$currentSettings['changeStatusFor'][Exchange\EntityType::SHIPMENT_NAME] = '';
			self::$currentSettings['changeStatusFor'][Exchange\EntityType::PAYMENT_CASH_NAME] = '';
			self::$currentSettings['changeStatusFor'][Exchange\EntityType::PAYMENT_CASH_LESS_NAME] = '';
			self::$currentSettings['changeStatusFor'][Exchange\EntityType::PAYMENT_CARD_TRANSACTION_NAME] = '';

			self::$currentSettings['importableFor'][Exchange\EntityType::USER_PROFILE_NAME] = Option::get("sale", "1C_IMPORT_NEW_ORDERS", "Y");
			self::$currentSettings['importableFor'][Exchange\EntityType::PROFILE_NAME] = Option::get("sale", "1C_IMPORT_NEW_ORDERS", "Y");
			self::$currentSettings['importableFor'][Exchange\EntityType::ORDER_NAME] = Option::get("sale", "1C_IMPORT_NEW_ORDERS", "Y");
			self::$currentSettings['importableFor'][Exchange\EntityType::SHIPMENT_NAME] = Option::get("sale", "1C_IMPORT_NEW_SHIPMENT", "Y");
			self::$currentSettings['importableFor'][Exchange\EntityType::PAYMENT_CASH_NAME] = Option::get("sale", "1C_IMPORT_NEW_PAYMENT", "Y");
			self::$currentSettings['importableFor'][Exchange\EntityType::PAYMENT_CASH_LESS_NAME] = Option::get("sale", "1C_IMPORT_NEW_PAYMENT", "Y");
			self::$currentSettings['importableFor'][Exchange\EntityType::PAYMENT_CARD_TRANSACTION_NAME] = Option::get("sale", "1C_IMPORT_NEW_PAYMENT", "Y");

			self::$currentSettings['accountNumberPrefix'][Exchange\EntityType::ORDER_NAME] = Option::get("sale", "1C_SALE_ACCOUNT_NUMBER_SHOP_PREFIX", "");
			self::$currentSettings['accountNumberPrefix'][Exchange\EntityType::SHIPMENT_NAME] = '';
			self::$currentSettings['accountNumberPrefix'][Exchange\EntityType::PAYMENT_CASH_NAME] = '';
			self::$currentSettings['accountNumberPrefix'][Exchange\EntityType::PAYMENT_CASH_LESS_NAME] = '';
			self::$currentSettings['accountNumberPrefix'][Exchange\EntityType::PAYMENT_CARD_TRANSACTION_NAME] = '';

			self::$currentSettings['paySystem'][Exchange\EntityType::PAYMENT_CASH_LESS_NAME] = Option::get("sale", "1C_IMPORT_DEFAULT_PS_B", "");
			self::$currentSettings['paySystem'][Exchange\EntityType::PAYMENT_CASH_NAME] = Option::get("sale", "1C_IMPORT_DEFAULT_PS", "");
			self::$currentSettings['paySystem'][Exchange\EntityType::PAYMENT_CARD_TRANSACTION_NAME] = Option::get("sale", "1C_IMPORT_DEFAULT_PS_A", "");

			self::$currentSettings['paySystemDefault'][Exchange\EntityType::PAYMENT_CASH_LESS_NAME] = Manager::getInnerPaySystemId();
			self::$currentSettings['paySystemDefault'][Exchange\EntityType::PAYMENT_CASH_NAME] = Manager::getInnerPaySystemId();
			self::$currentSettings['paySystemDefault'][Exchange\EntityType::PAYMENT_CARD_TRANSACTION_NAME] = Manager::getInnerPaySystemId();

			self::$currentSettings['shipmentService'][Exchange\EntityType::SHIPMENT_NAME] = Option::get("sale", "1C_IMPORT_DEFAULT_SHIPMENT_SERVICE", "");
			self::$currentSettings['shipmentServiceDefault'][Exchange\EntityType::SHIPMENT_NAME] = EmptyDeliveryService::getEmptyDeliveryServiceId();

			self::$currentSettings['canCreateOrder'][Exchange\EntityType::ORDER_NAME] = '';
			self::$currentSettings['canCreateOrder'][Exchange\EntityType::SHIPMENT_NAME] = Option::get("sale", "1C_IMPORT_NEW_ORDER_NEW_SHIPMENT", "");
			self::$currentSettings['canCreateOrder'][Exchange\EntityType::PAYMENT_CASH_NAME] = '';
			self::$currentSettings['canCreateOrder'][Exchange\EntityType::PAYMENT_CASH_LESS_NAME] = '';
			self::$currentSettings['canCreateOrder'][Exchange\EntityType::PAYMENT_CARD_TRANSACTION_NAME] = '';

			//self::$currentSettings['shipmentBasketChangeQuantity'][EntityType::SHIPMENT_NAME] = Option::get("sale", "1C_IMPORT_UPDATE_BASKET_QUANTITY", "");

			self::$currentSettings['collisionResolve'][Exchange\EntityType::ORDER_NAME] = Option::get("sale", "1C_IMPORT_COLLISION_RESOLVE", array(Exchange\EntityCollisionType::OrderFinalStatusName));
			self::$currentSettings['collisionResolve'][Exchange\EntityType::SHIPMENT_NAME] = Option::get("sale", "1C_IMPORT_COLLISION_RESOLVE", array(Exchange\EntityCollisionType::ShipmentIsShippedName));
			self::$currentSettings['collisionResolve'][Exchange\EntityType::PAYMENT_CASH_NAME] = Option::get("sale", "1C_IMPORT_COLLISION_RESOLVE", array(Exchange\EntityCollisionType::PaymentIsPayedName));
			self::$currentSettings['collisionResolve'][Exchange\EntityType::PAYMENT_CASH_LESS_NAME] = Option::get("sale", "1C_IMPORT_COLLISION_RESOLVE", array(Exchange\EntityCollisionType::PaymentIsPayedName));
			self::$currentSettings['collisionResolve'][Exchange\EntityType::PAYMENT_CARD_TRANSACTION_NAME] = Option::get("sale", "1C_IMPORT_COLLISION_RESOLVE", array(Exchange\EntityCollisionType::PaymentIsPayedName));

			if(!is_array(self::$currentSettings))
			{
				self::$currentSettings = array();
			}
		}
		return self::$currentSettings;
	}

	/**
	 * @param $entityTypeId
	 * @return bool
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotSupportedException
	 */
	public function isImportableFor($entityTypeId)
	{
		$entityTypeName = $this->resolveName($entityTypeId);
		return isset($this->settings['importableFor'][$entityTypeName]) && $this->settings['importableFor'][$entityTypeName] === 'Y';
	}

	/**
	 * @param $entityTypeId
	 * @return mixed
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotSupportedException
	 */
	public function paySystemIdFor($entityTypeId)
	{
		return $this->getValueFor($entityTypeId, 'paySystem');
	}

	/**
	 * @param $entityTypeId
	 * @return mixed
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotSupportedException
	 */
	public function paySystemIdDefaultFor($entityTypeId)
	{
		return $this->getValueFor($entityTypeId, 'paySystemDefault');
	}

	/**
	 * @param $entityTypeId
	 * @return mixed
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotSupportedException
	 */
	public function shipmentServiceFor($entityTypeId)
	{
		return $this->getValueFor($entityTypeId, 'shipmentService');
	}

	/**
	 * @param $entityTypeId
	 * @return mixed
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotSupportedException
	 */
	public function shipmentServiceDefaultFor($entityTypeId)
	{
		return $this->getValueFor($entityTypeId, 'shipmentServiceDefault');
	}

	/**
	 * @param $entityTypeId
	 * @return string
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotSupportedException
	 */
	public function finalStatusIdFor($entityTypeId)
	{
		return $this->getValueFor($entityTypeId, 'finalStatusId');
	}

	/**
	 * @param $entityTypeId
	 * @return string
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotSupportedException
	 */
	public function finalStatusOnDeliveryFor($entityTypeId)
	{
		return $this->getValueFor($entityTypeId, 'finalStatusOnDelivery');
	}

	/**
	 * @param $entityTypeId
	 * @return string
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotSupportedException
	 */
	public function changeStatusFor($entityTypeId)
	{
		$entityTypeName = $this->resolveName($entityTypeId);
		return ($this->settings['changeStatusFor'][$entityTypeName] == 'Y' ? $this->settings['changeStatusFor'][$entityTypeName]: '');
	}

	/**
	 * @param $entityTypeId
	 * @return string
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotSupportedException
	 */
	public function canCreateOrder($entityTypeId)
	{
		$entityTypeName = $this->resolveName($entityTypeId);
		return ($this->settings['canCreateOrder'][$entityTypeName] == 'Y' ? $this->settings['canCreateOrder'][$entityTypeName]: '');
	}

	/**
	 * @return string
	 */
	public function getSiteId()
	{
		return $this->settings['import']['SITE_ID'] !== ""  ?  $this->settings['import']['SITE_ID']: Main\Application::getInstance()->getContext()->getSite();
	}

	/**
	 * @return string
	 */
	public function getCurrency()
	{
		return $this->settings['import']['CURRENCY'];
	}

	/**
	 * @param $entityTypeId
	 * @return array
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotSupportedException
	 */
	public function getCollisionResolve($entityTypeId)
	{
		$entityTypeName = $this->resolveName($entityTypeId);
		return is_array($this->settings['collisionResolve'][$entityTypeName]) ? $this->settings['collisionResolve'][$entityTypeName]:array();
	}

	/**
	 * @return Exchange\ISettingsImport
	 */
	public static function getCurrent()
	{
		return new static(static::loadCurrentSettings());
	}
}