<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Component\BaseUfComponent;
use Bitrix\Main\UserField\Types\IntegerType;

/**
 * Class IntegerUfComponent
 */
class IntegerUfComponent extends BaseUfComponent
{
	protected static function getUserTypeId(): string
	{
		return IntegerType::USER_TYPE_ID;
	}
}