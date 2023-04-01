<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!CModule::IncludeModule("sale"))
	return false;

if(!CBXFeatures::IsFeatureEnabled('SaleReports'))
	return false;

$arSites = array("" => GetMessage("GD_ORDERS_P_SITE_ID_ALL"));

$dbSite = CSite::GetList("sort", "desc", Array("ACTIVE" => "Y"));
while($arSite = $dbSite->GetNext())
	$arSites[$arSite["LID"]] = "[".$arSite["LID"]."] ".$arSite["NAME"];

$arStatus1 = array(
	"CREATED" => GetMessage("GD_ORDERS_P_STATUS_1_CREATED"),
	"PAID" => GetMessage("GD_ORDERS_P_STATUS_1_PAID"),
	"CANCELED" => GetMessage("GD_ORDERS_P_STATUS_1_CANCELED"),
	"ALLOW_DELIVERY" => GetMessage("GD_ORDERS_P_STATUS_1_ALLOW_DELIVERY")
);
$arPeriod = array(
	"WEEK" => GetMessage("GD_ORDERS_P_WEEK"),
	"MONTH" => GetMessage("GD_ORDERS_P_MONTH"),
	"QUATER" => GetMessage("GD_ORDERS_P_QUATER"),
	"YEAR" => GetMessage("GD_ORDERS_P_YEAR")
);

$arParameters = Array(
	"PARAMETERS"=> Array(),
	"USER_PARAMETERS"=> Array(
		"SITE_ID" => Array(
			"NAME" => GetMessage("GD_ORDERS_P_SITE_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arSites,
			"MULTIPLE" => "N",
			"DEFAULT" => ""
		),
		"ORDERS_STATUS" => Array(
			"NAME" => GetMessage("GD_ORDERS_P_STATUS_1"),
			"TYPE" => "LIST",
			"VALUES" => $arStatus1,
			"MULTIPLE" => "Y",
			"DEFAULT" => array("CREATED", "PAID")
		),
		"PERIOD" => Array(
			"NAME" => GetMessage("GD_ORDERS_P_PERIOD"),
			"TYPE" => "LIST",
			"VALUES" => $arPeriod,
			"DEFAULT" => "MONTH"
		),
	)
);
