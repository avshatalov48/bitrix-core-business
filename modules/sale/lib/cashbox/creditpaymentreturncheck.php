<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main;

Main\Localization\Loc::loadMessages(__FILE__);

/**
 * Class CreditPaymentReturnCheck
 * @package Bitrix\Sale\Cashbox
 */

class CreditPaymentReturnCheck extends Check
{
	/**
	 * @return string
	 */
	public static function getType()
	{
		return 'creditpaymentreturn';
	}

	/**
	 * @return string
	 */
	public static function getName()
	{
		return Main\Localization\Loc::getMessage('SALE_CASHBOX_CREDIT_PAYMENT_RETURN_NAME');
	}

	/**
	 * @return string
	 */
	public static function getCalculatedSign()
	{
		return static::CALCULATED_SIGN_CONSUMPTION;
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
		$result['PRODUCTS'] = array(
			array(
				'NAME' => Main\Localization\Loc::getMessage('SALE_CASHBOX_CREDIT_PAYMENT_RETURN_ITEM_NAME'),
				'QUANTITY' => 1,
				'PRICE' => $result['TOTAL_SUM'],
				'SUM' => $result['TOTAL_SUM'],
				'BASE_PRICE' => $result['TOTAL_SUM'],
				'PAYMENT_OBJECT' => static::PAYMENT_OBJECT_PAYMENT,
			)
		);

		if (isset($result['PAYMENTS']))
		{
			foreach ($result['PAYMENTS'] as $i => $payment)
			{
				$result['PAYMENTS'][$i]['IS_CASH'] = 'N';
				$result['PAYMENTS'][$i]['TYPE'] = static::PAYMENT_TYPE_CASHLESS;
			}
		}

		return $result;
	}
}