<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arComponentDescription = array(
	"NAME" => Loc::getMessage("RMP_COMP_NAME_2"),
	"DESCRIPTION" => Loc::getMessage("RMP_COMP_DESCR_2"),
	"ICON" => "/images/icon.gif",
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "b24marketplace",
			"NAME" => Loc::getMessage("RMP_PATH_B24MP_DESCR_2"),
		)
	),
//	"CACHE_PATH" => "Y",
	"COMPLEX" => "Y"
);