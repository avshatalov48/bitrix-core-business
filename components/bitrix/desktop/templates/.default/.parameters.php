<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/desktop/include.php');

$arGadgetsFixed = Array();

$arGadgets = BXGadget::GetList();

foreach($arGadgets as $gd)
{
	if ($gd["SU_ONLY"] == true || $gd["SG_ONLY"] == true)
		continue;
		
	if (!array_key_exists("CAN_BE_FIXED", $gd) || !$gd["CAN_BE_FIXED"])
		continue;

	if ($gd["EXTRANET_ONLY"] == true && (!CModule::IncludeModule('extranet') || !CExtranet::IsExtranetSite($_REQUEST["src_site"])))
		continue;
	if ($gd["SEARCH_ONLY"] == true && !IsModuleInstalled("search"))
		continue;
	if ($gd["FORUM_ONLY"] == true && !IsModuleInstalled("forum"))
		continue;
	if ($gd["BLOG_ONLY"] == true && !IsModuleInstalled("blog"))
		continue;
	if ($gd["PHOTOGALLERY_ONLY"] == true && !IsModuleInstalled("photogallery"))
		continue;
	if ($gd["WEBDAV_ONLY"] == true && !IsModuleInstalled("webdav"))
		continue;
	if ($gd["DISK_ONLY"] == true && !IsModuleInstalled("disk"))
		continue;
	if ($gd["VOTE_ONLY"] == true && !IsModuleInstalled("vote"))
		continue;	
	
	$arGadgetsFixed[$gd["ID"]] = $gd["NAME"];
}

if (!empty($arGadgetsFixed))
{
	$arTemplateParameters["GADGETS_FIXED"] = Array(
			"NAME" => GetMessage("CMDESKTOP_PARAMS_GADGETS_FIXED"),
			"TYPE" => "LIST",
			"DEFAULT" => array(),
			"MULTIPLE" => "Y",
			"SIZE"=>"10",
			"REFRESH" => "N",
			"VALUES" => $arGadgetsFixed,
		);
}
?>