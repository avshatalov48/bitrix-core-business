<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main;

Main\Localization\Loc::loadMessages(__FILE__);

/**
 * Class FullPrepaymentReturnCheck
 * @package Bitrix\Sale\Cashbox
 */
class FullPrepaymentReturnCheck extends FullPrepaymentCheck
{
	/**
	 * @return string
	 */
	public static function getType()
	{
		return 'fullprepaymentreturn';
	}

	/**
	 * @throws Main\NotImplementedException
	 * @return string
	 */
	public static function getCalculatedSign()
	{
		return static::CALCULATED_SIGN_CONSUMPTION;
	}

	/**
	 * @return string
	 */
	public static function getName()
	{
		return Main\Localization\Loc::getMessage('SALE_CASHBOX_FULLPREPAYMENT_RETURN_NAME');
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