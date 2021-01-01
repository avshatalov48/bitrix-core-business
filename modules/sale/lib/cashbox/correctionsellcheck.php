<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main;

/**
 * Class CorrectionSellCheck
 * @package Bitrix\Sale\Cashbox
 */
class CorrectionSellCheck extends CorrectionCheck
{
	public static function getName()
	{
		return Main\Localization\Loc::getMessage('SALE_CASHBOX_CORRECTION_SELL_NAME');
	}

	/**
	 * @return string
	 */
	public static function getType()
	{
		return 'correction_sell';
	}

	/**
	 * @return string
	 */
	public static function getCalculatedSign()
	{
		return static::CALCULATED_SIGN_INCOME;
	}
}
