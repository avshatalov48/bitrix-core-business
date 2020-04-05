<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!CModule::IncludeModule("sale"))
	return false;

global $APPLICATION, $USER;

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
$userGroups = $USER->GetUserGroupArray();

$accessibleSites = array();
if ($saleModulePermissions != "W")
{

	$resAccessibleSites = CSaleGroupAccessToSite::GetList(
		array(),
		array("GROUP_ID" => $userGroups),
		false,
		false,
		array("SITE_ID")
	);
	while ($accessibleSiteData = $resAccessibleSites->Fetch())
	{
		if(!in_array($accessibleSiteData["SITE_ID"], $accessibleSites))
		{
			$accessibleSites[] = $accessibleSiteData["SITE_ID"];
		}
	}
}


$arSites = array("" => GetMessage("GD_ORDERS_P_SITE_ID_ALL"));

$dbSite = CSite::GetList($by1="sort", $order1="desc", array('ACTIVE' => 'Y'));
while($arSite = $dbSite->GetNext())
{
	if ($saleModulePermissions != "W")
	{
		if(!in_array($arSite["LID"], $accessibleSites))
		{
			continue;
		}
	}
	$arSites[$arSite["LID"]] = "[".$arSite["LID"]."] ".$arSite["NAME"];
}

$arStatus1 = array(
	"CREATED"					=> GetMessage("GD_ORDERS_P_STATUS_1_CREATED"),
	"PAID"							=> GetMessage("GD_ORDERS_P_STATUS_1_PAID"),
	"CANCELED"				=> GetMessage("GD_ORDERS_P_STATUS_1_CANCELED"),
	"ALLOW_DELIVERY"		=> GetMessage("GD_ORDERS_P_STATUS_1_ALLOW_DELIVERY")
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
		"ORDERS_STATUS_1" => Array(
			"NAME" => GetMessage("GD_ORDERS_P_STATUS_1"),
			"TYPE" => "LIST",
			"VALUES" => $arStatus1,
			"MULTIPLE" => "Y",
			"DEFAULT" => array("CREATED", "PAID", "CANCELED", "ALLOW_DELIVERY")
		),
		"ITEMS_COUNT" => Array(
			"NAME" => GetMessage("GD_ORDERS_P_ITEMS_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => "10"
		)		
	)
);
?>