<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main;
use Bitrix\Sale\Payment;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\ShipmentItem;

Main\Localization\Loc::loadMessages(__FILE__);

/**
 * Class CreditPaymentCheck
 * @package Bitrix\Sale\Cashbox
 */

class CreditPaymentCheck extends Check
{
	/**
	 * @return string
	 */
	public static function getType()
	{
		return 'creditpayment';
	}

	/**
	 * @return string
	 */
	public static function getName()
	{
		return Main\Localization\Loc::getMessage('SALE_CASHBOX_CREDIT_PAYMENT_NAME');
	}

	/**
	 * @return string
	 */
	public static function getCalculatedSign()
	{
		return static::CALCULATED_SIGN_INCOME;
	}

	/**
	 * @return string
	 */
	public static function getSupportedRelatedEntityType()
	{
		return static::SUPPORTED_ENTITY_TYPE_SHIPMENT;
	}

}