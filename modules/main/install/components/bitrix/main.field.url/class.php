<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Component\BaseUfComponent;
use Bitrix\Main\UserField\Types\UrlType;

/**
 * Class UrlUfComponent
 */
class UrlUfComponent extends BaseUfComponent
{
	protected static function getUserTypeId(): string
	{
		return UrlType::USER_TYPE_ID;
	}
}