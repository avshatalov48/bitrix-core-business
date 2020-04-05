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
			"application" => array(
				"NAME" => Loc::getMessage("RMP_SEF_APPLICATION"),
				"DEFAULT" => "#id#/",
				"VARIABLES" => array(),
			),
		),
		"DETAIL_URL" => array(
			"PARENT" => "BASE",
			"NAME" => Loc::getMessage("RMP_DETAIL_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => '/marketplace/detail/#code#/'
		),

//		"SET_TITLE" => array(),
//		"CACHE_TIME" => array(),
	),
);