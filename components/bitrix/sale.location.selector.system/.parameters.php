<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arComponentParameters = Array(
	"PARAMETERS" => Array(

		"ENTITY_PRIMARY" => Array(
			"NAME" => Loc::getMessage("SALE_SLSS_ENTITY_PRIMARY_PARAMETER"),
			"PARENT" => "BASE",
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
		),
		"INPUT_NAME" => Array(
			"NAME" => Loc::getMessage("SALE_SLSS_INPUT_NAME_PARAMETER"),
			"PARENT" => "BASE",
			"TYPE" => "STRING",
			"DEFAULT" => "LOCATION",
		),

		"LINK_ENTITY_NAME" => Array(
			"NAME" => Loc::getMessage("SALE_SLSS_LINK_ENTITY_NAME_PARAMETER"),
			"PARENT" => "BASE",
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
		),

		"ENTITY_VARIABLE_NAME" => Array(
			"NAME" => Loc::getMessage("SALE_SLSS_ENTITY_VARIABLE_NAME_PARAMETER"),
			"PARENT" => "BASE",
			"TYPE" => "STRING",
			"DEFAULT" => 'id',
			"MULTIPLE" => "N",
		),

		"CACHE_TIME" => array("DEFAULT" => 36000000)
	)
);