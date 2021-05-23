<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arComponentParameters = array(
	"PARAMETERS" => array(
		"VARIABLE_ALIASES" => array(
			"id" => array(
				"NAME" => Loc::getMessage("RMP_VA_ID"),
			),
		),
		"SEF_MODE" => array(
			"index" => array(
				"NAME" => Loc::getMessage("RMP_SEF_INDEX"),
				"DEFAULT" => "",
				"VARIABLES" => array(),
			),
			"list" => array(
				"NAME" => Loc::getMessage("RMP_SEF_LIST"),
				"DEFAULT" => "list/",
				"VARIABLES" => array(),
			),
			"edit" => array(
				"NAME" => Loc::getMessage("RMP_SEF_EDIT"),
				"DEFAULT" => "edit/#id#/",
				"VARIABLES" => array("id"),
			),
		),
		"APPLICATION_URL" => array(
			"PARENT" => "BASE",
			"NAME" => Loc::getMessage("RMP_APPLICATION_PAGE"),
			"TYPE" => "STRING",
			"DEFAULT" => '/marketplace/app/#id#/'
		),

//		"SET_TITLE" => array(),
//		"CACHE_TIME" => array(),
	),
);