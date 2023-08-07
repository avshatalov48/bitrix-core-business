<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$rsSite = CSite::GetList("sort", "asc", $arFilter=array("ACTIVE" => "Y"));
$arSites = array("*all*" => GetMessage("SITE_SELECTOR_SITES_ALL"));
while ($arSite = $rsSite->GetNext())
{
	$arSites[$arSite["LID"]] = $arSite["NAME"];
}

$arComponentParameters = array(
	"PARAMETERS" => array(
		"SITE_LIST" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("SITE_SELECTOR_SITE_LIST"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "N",
			"VALUES" => $arSites
		),
		
		"CACHE_TIME" => array("DEFAULT" => "3600"),		
	),
);
?>