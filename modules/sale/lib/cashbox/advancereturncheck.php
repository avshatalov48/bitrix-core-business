<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main;

Main\Localization\Loc::loadMessages(__FILE__);

/**
 * Class AdvanceReturnCheck
 * @package Bitrix\Sale\Cashbox
 */

class AdvanceReturnCheck extends AdvancePaymentCheck
{
	/**
	 * @return string
	 */
	public static function getType()
	{
		return 'advancereturn';
	}

	/**
	 * @return string
	 */
	public static function getName()
	{
		return Main\Localization\Loc::getMessage('SALE_CASHBOX_ADVANCE_RETURN_NAME');
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
				$result['PAYMENTS'][$i]['IS_CASH'] = 'N';
				$result['PAYMENTS'][$i]['TYPE'] = static::PAYMENT_TYPE_CASHLESS;
			}
		}

		return $result;
	}

}