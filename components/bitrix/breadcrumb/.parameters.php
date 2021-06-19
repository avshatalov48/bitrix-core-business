<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arSites = Array("-" => "");
$rsSites = CSite::GetList("sort", "desc");
while ($arSite = $rsSites->GetNext())
	$arSites[$arSite["ID"]] = "[".$arSite["ID"]."] ".$arSite["NAME"];


$arComponentParameters = array(
	"PARAMETERS" => array(

		"START_FROM" => Array(
			"NAME"=>GetMessage("START_FROM_NAME"),
			"TYPE" => "TEXT",
			"DEFAULT"=>'0',
			"PARENT" => "ADDITIONAL_SETTINGS",
		),

		"PATH" => Array(
			"NAME"=>GetMessage("BREADCRUMB_PATH_NAME"),
			"TYPE" => "TEXT",
			"DEFAULT"=>'',
			"PARENT" => "ADDITIONAL_SETTINGS",
		),

		"SITE_ID" => Array(
			"NAME"=>GetMessage("BREADCRUMB_SITE_ID"),
			"TYPE" => "LIST",
			"DEFAULT"=>'-',
			"VALUES" => $arSites,
			"ADDITIONAL_VALUES" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
	)
);
?>
