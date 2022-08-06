<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Component\BaseUfComponent;
use Bitrix\Fileman\UserField\Types\AddressType;

\Bitrix\Main\Loader::includeModule('location');

/**
 * Class AddressUfComponent
 */
class AddressUfComponent extends BaseUfComponent
{
	protected static function getUserTypeId(): string
	{
		return AddressType::USER_TYPE_ID;
	}
}
