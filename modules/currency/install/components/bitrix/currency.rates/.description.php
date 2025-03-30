<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arComponentDescription = [
	"NAME" => Loc::getMessage("CURRENCY_SHOW_RATES_COMPONENT_NAME"),
	"DESCRIPTION" => Loc::getMessage("CURRENCY_SHOW_RATES_COMPONENT_DESCRIPTION"),
	"ICON" => "/images/currency_rates.gif",
	"CACHE_PATH" => "Y",
	"PATH" => [
		"ID" => "content",
		"CHILD" => [
			"ID" => "CURRENCY",
			"NAME" => Loc::getMessage("CURRENCY_GROUP_NAME"),
		],
	],
];
