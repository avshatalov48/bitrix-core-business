<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var array $arCurrentValues */

use Bitrix\Main\Loader;

if (!Loader::includeModule('iblock'))
{
	return;
}

if (($arCurrentValues["SITE"] ?? '') !== '')
{
	$url_default = "http://" . $arCurrentValues["SITE"];

	$port = (int)($arCurrentValues["PORT"] ?? 0);
	if ($port > 0 && $port !== 80)
	{
		$url_default .= ":" . $port;
	}

	if (($arCurrentValues["PATH"] ?? '') !== '')
	{
		$url_default .= "/" . ltrim((string)$arCurrentValues["PATH"], "/");
	}

	if (($arCurrentValues["QUERY_STR"] ?? '') !== '')
	{
		$url_default .= "?" . ltrim((string)$arCurrentValues["QUERY_STR"], "?");
	}
}
else
{
	$url_default = "";
}


$arComponentParameters = [
	"GROUPS" => [],
	"PARAMETERS" => [
		"URL" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BRS_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => $url_default,
		],
		"OUT_CHANNEL" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("T_IBLOCK_DESC_RSS_OUT_CHANNEL"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		],
		"NUM_NEWS" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("T_IBLOCK_DESC_RSS_NUM_NEWS"),
			"TYPE" => "STRING",
			"DEFAULT" => '10',
		],
		"PROCESS" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BRS_PROCESS"),
			"TYPE" => "LIST",
			"DEFAULT" => "NONE",
			"VALUES" => [
				"NONE" => GetMessage("CP_BRS_PROCESS_NONE"),
				"TEXT" => GetMessage("CP_BRS_PROCESS_TEXT"),
				"QUOTE" => GetMessage("CP_BRS_PROCESS_QUOTE"),
			],
		],
		"CACHE_TIME"  =>  ["DEFAULT"=>3600],
	],
];
