<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Component\BaseUfComponent;
use Bitrix\Main\UserField\Types\StringFormattedType;

/**
 * Class StringFormatUfComponent
 */
class StringFormattedUfComponent extends BaseUfComponent
{
	protected static function getUserTypeId(): string
	{
		return StringFormattedType::USER_TYPE_ID;
	}
}