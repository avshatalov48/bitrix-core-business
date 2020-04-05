<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main;

Main\Localization\Loc::loadMessages(__FILE__);

/**
 * Class AdvanceReturnCashCheck
 * @package Bitrix\Sale\Cashbox
 */

class AdvanceReturnCashCheck extends AdvancePaymentCheck
{
	/**
	 * @return string
	 */
	public static function getType()
	{
		return 'advancereturncash';
	}

	/**
	 * @return string
	 */
	public static function getName()
	{
		return Main\Localization\Loc::getMessage('SALE_CASHBOX_ADVANCE_RETURN_CASH_NAME');
	}

	/**
	 * @return string
	 */
	public static function getCalculatedSign()
	{
		return static::CALCULATED_SIGN_CONSUMPTION;
	}


	/**
	 * @return array
	 */
	protected function extractDataInternal()
	{
		$result = parent::extractDataInternal();

		if (isset($result['PAYMENTS']))
		{
			foreach ($result['PAYMENTS'] as $i => $payment)
			{
				$result['PAYMENTS'][$i]['IS_CASH'] = 'Y';
				$result['PAYMENTS'][$i]['TYPE'] = static::PAYMENT_TYPE_CASH;
			}

		}

		return $result;
	}
}