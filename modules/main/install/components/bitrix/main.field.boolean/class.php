<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Component\BaseUfComponent;
use Bitrix\Main\UserField\Types\BooleanType;

/**
 * Class BooleanUfComponent
 */
class BooleanUfComponent extends BaseUfComponent
{
	protected static function getUserTypeId(): string
	{
		return BooleanType::USER_TYPE_ID;
	}
}