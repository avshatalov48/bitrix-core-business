<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main;

/**
 * Class CorrectionBuyCheck
 * @package Bitrix\Sale\Cashbox
 */
class CorrectionBuyCheck extends CorrectionCheck
{
	public static function getName()
	{
		return Main\Localization\Loc::getMessage('SALE_CASHBOX_CORRECTION_BUY_NAME');
	}

	/**
	 * @return string
	 */
	public static function getType()
	{
		return 'correction_buy';
	}

	/**
	 * @return string
	 */
	public static function getCalculatedSign()
	{
		return static::CALCULATED_SIGN_CONSUMPTION;
	}
}
