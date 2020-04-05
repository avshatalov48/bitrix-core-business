<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/main.share/util.php");

/**
 * Come from GetTemplateProps()
 * @param string $templateName
 * @param string $siteTemplate
 * @param array $arCurrentValues
 */
$arHandlers = __bx_share_get_handlers($templateName, $siteTemplate);

$arTemplateParameters = array(
	"HANDLERS" => array(
		"NAME" => GetMessage("BOOKMARK_SYSTEM"),
		"TYPE" => "LIST",
		"MULTIPLE" => "Y",
		"VALUES" => $arHandlers["HANDLERS"],
		"DEFAULT" => $arHandlers["HANDLERS_DEFAULT"],
		"REFRESH"=> "Y",
	),
	"PAGE_URL" => array(
		"NAME" => GetMessage("BOOKMARK_URL"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
	),
	"PAGE_TITLE" => array(
		"NAME" => GetMessage("BOOKMARK_TITLE"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
	),
);

if (
	(
		is_array($arCurrentValues["HANDLERS"])
		&& in_array("twitter", $arCurrentValues["HANDLERS"])
	)
	|| (
		empty($arCurrentValues["HANDLERS"])
		&& is_array($arHandlers["HANDLERS_DEFAULT"])
		&& in_array("twitter", $arHandlers["HANDLERS_DEFAULT"])
	)
)
{
	$arTemplateParameters["SHORTEN_URL_LOGIN"] = array(
		"NAME" => GetMessage("BOOKMARK_SHORTEN_URL_LOGIN"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
	);
	
	$arTemplateParameters["SHORTEN_URL_KEY"] = array(
		"NAME" => GetMessage("BOOKMARK_SHORTEN_URL_KEY"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
	);
}
