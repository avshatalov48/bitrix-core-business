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
			"category" => array(
				"NAME" => Loc::getMessage("RMP_VA_CATEGORY"),
			),
			"app" => array(
				"NAME" => Loc::getMessage("RMP_VA_CODE"),
			),
		),
		"SEF_MODE" => array(
			"top" => array(
				"NAME" => Loc::getMessage("RMP_SEF_TOP"),
				"DEFAULT" => "",
				"VARIABLES" => array(),
			),
			"category" => array(
				"NAME" => Loc::getMessage("RMP_SEF_CATEGORY"),
				"DEFAULT" => "category/#category#/",
				"VARIABLES" => array('category'),
			),
			"detail" => array(
				"NAME" => Loc::getMessage("RMP_SEF_DETAIL"),
				"DEFAULT" => "detail/#app#/",
				"VARIABLES" => array("app"),
			),
			"search" => array(
				"NAME" => Loc::getMessage("RMP_SEF_SEARCH"),
				"DEFAULT" => "search/",
				"VARIABLES" => array(),
			),
			"buy" => array(
				"NAME" => Loc::getMessage("RMP_SEF_BUY"),
				"DEFAULT" => "buy/",
				"VARIABLES" => array(),
			),
			"updates" => array(
				"NAME" => Loc::getMessage("RMP_SEF_UPDATES"),
				"DEFAULT" => "updates/",
				"VARIABLES" => array(),
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