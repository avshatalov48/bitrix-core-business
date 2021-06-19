<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main;

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
		return static::SUPPORTED_ENTITY_TYPE_NONE;
	}

	/**
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\LoaderException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	protected function extractDataInternal()
	{
		$result = parent::extractDataInternal();

		unset($result['DELIVERY']);
		$result['PRODUCTS'] = [
			[
				'NAME' => Main\Localization\Loc::getMessage('SALE_CASHBOX_CREDIT_PAYMENT_ITEM_NAME'),
				'QUANTITY' => 1,
				'PRICE' => $result['TOTAL_SUM'],
				'SUM' => $result['TOTAL_SUM'],
				'BASE_PRICE' => $result['TOTAL_SUM'],
				'PAYMENT_OBJECT' => static::PAYMENT_OBJECT_PAYMENT,
			]
		];

		return $result;
	}

	protected function needPrintMarkingCode($basketItem) : bool
	{
		return false;
	}
}