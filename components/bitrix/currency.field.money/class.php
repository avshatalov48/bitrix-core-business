<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Component\BaseUfComponent;
use Bitrix\Currency\UserField\Types\MoneyType;
use Bitrix\Main\Localization\Loc;

/**
 * Class MoneyUfComponent
 */
class MoneyUfComponent extends BaseUfComponent
{

	/**
	 * @return string
	 */
	protected static function getUserTypeId(): string
	{
		return MoneyType::USER_TYPE_ID;
	}

	/**
	 * @return string
	 */
	public function getEmptyValueCaption(): string
	{
		return Loc::getMessage('USER_TYPE_MONEY_NO_VALUE');
	}

	/**
	 * @return string
	 */
	public function getEmptyCurrencyCaption(): string
	{
		return Loc::getMessage('USER_TYPE_MONEY_NO_CURRENCY');
	}
}