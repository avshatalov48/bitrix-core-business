<?php

use Bitrix\Fileman\UserField\Types\AddressType;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

foreach($arResult['value'] as $key => $value)
{
	if($value)
	{
		$arResult['value'][$key] = AddressType::getAddressFieldsByValue($value);
	}
}
