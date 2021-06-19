<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!CModule::IncludeModule("sale"))
	return false;

$arSites = array("" => GetMessage("GD_PRD_P_SITE_ID_ALL"));

$dbSite = CSite::GetList("sort", "desc", Array("ACTIVE" => "Y"));
while($arSite = $dbSite->GetNext())
	$arSites[$arSite["LID"]] = "[".$arSite["LID"]."] ".$arSite["NAME"];

$arPeriod = array(
	"WEEK" => GetMessage("GD_PRD_P_WEEK"),
	"MONTH" => GetMessage("GD_PRD_P_MONTH"),
	"QUATER" => GetMessage("GD_PRD_P_QUATER"),
	"YEAR" => GetMessage("GD_PRD_P_YEAR")
);

$arParameters = Array(
	"PARAMETERS"=> Array(),
	"USER_PARAMETERS"=> Array(
		"SITE_ID" => Array(
			"NAME" => GetMessage("GD_PRD_P_SITE_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arSites,
			"MULTIPLE" => "N",
			"DEFAULT" => ""
		),
		"PERIOD" => Array(
			"NAME" => GetMessage("GD_PRD_P_PERIOD"),
			"TYPE" => "LIST",
			"VALUES" => $arPeriod,
			"DEFAULT" => "MONTH"
		),
		"LIMIT" => Array(
			"NAME" => GetMessage("GD_PRD_P_LIMIT"),
			"TYPE" => "STRING",
			"DEFAULT" => 5
		),
	)
);
?>