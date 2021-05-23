<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Component\BaseUfComponent;
use Bitrix\Main\UserField\Types\DoubleType;

/**
 * Class DoubleUfComponent
 */
class DoubleUfComponent extends BaseUfComponent
{
	protected static function getUserTypeId(): string
	{
		return DoubleType::USER_TYPE_ID;
	}
}