<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var array $arCurrentValues */
/** @global CUserTypeManager $USER_FIELD_MANAGER */

use Bitrix\Main\Loader;

if (!Loader::includeModule('iblock'))
{
	return;
}

$arIBlockType = CIBlockParameters::GetIBlockTypes();

$arComponentParameters = [
	"GROUPS" => [],
	"PARAMETERS" => [
		"IBLOCK_TYPE" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y",
		],
		"IBLOCK_URL" => CIBlockParameters::GetPathTemplateParam(
			"LIST",
			"IBLOCK_URL",
			GetMessage("IBLOCK_LIST_URL"),
			"",
			"URL_TEMPLATES",
		),
		"CACHE_TIME"  =>  ["DEFAULT"=>36000000],
		"CACHE_GROUPS" => [
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("CP_BCM_CACHE_GROUPS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		],
	],
];
