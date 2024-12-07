<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
$arComponentDescription = [
	"NAME" => GetMessage("BITRIXTVBIG_COMPONENT_NAME"),
	"DESCRIPTION" => GetMessage("BITRIXTVBIG_COMPONENT_DESCRIPTION"),
	"ICON" => "/images/bitrix_tv.gif",
	"COMPLEX" => "N",
	"PATH" => [
		"ID" => "content",
		"CHILD" => [
			"ID" => "media",
			"NAME" => GetMessage("BITRIXTVBIG_COMPONENTS"),
		],
	],
];
