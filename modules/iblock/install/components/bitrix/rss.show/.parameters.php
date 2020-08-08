<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;

if($arCurrentValues["SITE"] <> '')
{
	$url_default = "http://".$arCurrentValues["SITE"];

	$port = intval($arCurrentValues["PORT"]);
	if($port > 0 && $port != 80)
		$url_default .= ":".$port;

	if($arCurrentValues["PATH"] <> '')
		$url_default .= "/".ltrim($arCurrentValues["PATH"], "/");

	if($arCurrentValues["QUERY_STR"] <> '')
		$url_default .= "?".ltrim($arCurrentValues["QUERY_STR"], "?");
}
else
{
	$url_default = "";
}


$arComponentParameters = array(
	"GROUPS" => array(
	),
	"PARAMETERS" => array(
		"URL" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BRS_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => $url_default,
		),
		"OUT_CHANNEL" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("T_IBLOCK_DESC_RSS_OUT_CHANNEL"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"NUM_NEWS" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("T_IBLOCK_DESC_RSS_NUM_NEWS"),
			"TYPE" => "STRING",
			"DEFAULT" => '10',
		),
		"PROCESS" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BRS_PROCESS"),
			"TYPE" => "LIST",
			"DEFAULT" => "NONE",
			"VALUES" => array(
				"NONE" => GetMessage("CP_BRS_PROCESS_NONE"),
				"TEXT" => GetMessage("CP_BRS_PROCESS_TEXT"),
				"QUOTE" => GetMessage("CP_BRS_PROCESS_QUOTE"),
			),
		),
		"CACHE_TIME"  =>  Array("DEFAULT"=>3600),
	),
);
?>
