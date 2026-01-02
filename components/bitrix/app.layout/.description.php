<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arComponentDescription = array(
	"NAME" => Loc::getMessage("RMP_LAYOUT_COMP_NAME_2"),
	"DESCRIPTION" => Loc::getMessage("RMP_LAYOUT_COMP_DESCR_2"),
	"ICON" => "/images/icon.gif",
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "b24marketplace",
			"NAME" => \Bitrix\Rest\Integration\Market\Label::isRenamedMarket() ? Loc::getMessage("RMP_PATH_B24MP_DESCR_2_MSGVER_1") : Loc::getMessage("RMP_PATH_B24MP_DESCR_2_MSGVER_2"),
		)
	),
//	"CACHE_PATH" => "Y",
);