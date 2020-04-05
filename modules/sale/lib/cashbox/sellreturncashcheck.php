<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\NotImplementedException;

Loc::loadMessages(__FILE__);

/**
 * Class SellReturnCashCheck
 * @package Bitrix\Sale\Cashbox
 */
class SellReturnCashCheck extends SellCheck
{
	/**
	 * @return string
	 */
	public static function getType()
	{
		return 'sellreturncash';
	}

	/**
	 * @throws NotImplementedException
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
		return Loc::getMessage('SALE_CASHBOX_SELL_RETURN_CASH_NAME');
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

	/**
	 * @return string
	 */
	public static function getSupportedEntityType()
	{
		return static::SUPPORTED_ENTITY_TYPE_PAYMENT;
	}
}